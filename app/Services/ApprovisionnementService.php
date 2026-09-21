<?php

namespace App\Services;

use App\Helpers\ApprovisionnementHelper;
use App\Models\Approvisionnement;
use App\Models\BonReception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ApprovisionnementService
{
    // ── Chef de Projet ──────────────────────────────────────
    public function creerPlusieurs(array $data, int $demandeurId): void
    {
        DB::transaction(function () use ($data, $demandeurId) {
            foreach ($data['demandes'] as $item) {
                Approvisionnement::create([
                    'designation'              => $item['designation'],
                    'quantite_demandee'        => $item['quantite_demandee'],
                    'unite'                    => $item['unite'],
                    'date_livraison_souhaitee' => $item['date_livraison_souhaitee'],
                    'priorite'                 => ApprovisionnementHelper::calculerPriorite($item['date_livraison_souhaitee']),
                    'statutAppro'              => 'en_attente',
                    'chantier_id'              => $data['chantier_id'],
                    'demandeur_id'             => $demandeurId,
                ]);
            }
        });
    }

    public function modifier(Approvisionnement $appro, array $data): Approvisionnement
    {
        if ($appro->statutAppro !== 'en_attente') {
            throw new \Exception("Impossible de modifier une demande qui n'est plus en attente.");
        }

        $appro->update([
            'designation'              => $data['designation'],
            'quantite_demandee'        => $data['quantite_demandee'],
            'unite'                    => $data['unite'],
            'priorite'                 => ApprovisionnementHelper::calculerPriorite($data['date_livraison_souhaitee']),
            'date_livraison_souhaitee' => $data['date_livraison_souhaitee'],
            'chantier_id'              => $data['chantier_id'],
        ]);

        return $appro->fresh();
    }

    public function supprimer(Approvisionnement $appro): void
    {
        if (!in_array($appro->statutAppro, ['en_attente', 'validee'])) {
            throw new \Exception("Impossible de supprimer une demande en cours de livraison ou clôturée.");
        }

        $appro->delete();
    }

    // ── DAF ─────────────────────────────────────────────────
    public function valider(Approvisionnement $appro): Approvisionnement
    {
        $this->verifierStatut($appro, 'en_attente', 'valider');
        $appro->update(['statutAppro' => 'validee']);
        return $appro->fresh();
    }

    public function rejeter(Approvisionnement $appro): Approvisionnement
    {
        $this->verifierStatut($appro, 'en_attente', 'rejeter');
        $appro->update(['statutAppro' => 'rejetee']);
        return $appro->fresh();
    }

    public function commander(Approvisionnement $appro, ?string $dateLivraisonPrevue = null): Approvisionnement
    {
        $this->verifierStatut($appro, 'validee', 'commander');
        $appro->update([
            'statutAppro'           => 'en_cours_livraison',
            'date_commande'         => now()->toDateString(),
            'date_livraison_prevue' => $dateLivraisonPrevue,
        ]);
        return $appro->fresh();
    }

    public function definirDateLivraisonPrevue(Approvisionnement $appro, ?string $date): Approvisionnement
    {
        if (!in_array($appro->statutAppro, ['en_cours_livraison', 'partiellement_recue'])) {
            throw new \Exception("La date de livraison prévue ne peut être définie que pour une commande en cours de livraison.");
        }

        $appro->update(['date_livraison_prevue' => $date]);
        return $appro->fresh();
    }


    private function verifierStatut(Approvisionnement $appro, string $statutAttendu, string $action): void
    {
        if ($appro->statutAppro !== $statutAttendu) {
            throw new \Exception("Impossible de {$action} une demande au statut '{$appro->statutAppro}'.");
        }
    }

    // ── Pointeur ────────────────────────────────────────────
    public function receptionner(Approvisionnement $appro, array $data, int $pointeurId): BonReception
    {
        if (!in_array($appro->statutAppro, ['en_cours_livraison', 'partiellement_recue'])) {
            throw new \Exception("Cette demande ne peut pas être réceptionnée.");
        }

        return DB::transaction(function () use ($appro, $data, $pointeurId) {
            $appro = Approvisionnement::whereKey($appro->id)->lockForUpdate()->firstOrFail();

            $quantiteRecue    = (float) $data['quantite_recue'];
            $quantiteRestante = ApprovisionnementHelper::quantiteRestante($appro);

            if ($quantiteRecue > $quantiteRestante) {
                throw new \Exception("La quantité reçue ({$quantiteRecue}) dépasse la quantité restante à livrer ({$quantiteRestante}).");
            }

            $bon = BonReception::create([
                'demande_id'          => $appro->id,
                'chantier_id'         => $appro->chantier_id,
                'receptionnee_par_id' => $pointeurId,
                'quantite_recue'      => $quantiteRecue,
                'date_reception'      => now()->toDateString(),
                'observation'         => $data['observation'] ?? null,
            ]);

            $appro->update([
                'statutAppro' => ApprovisionnementHelper::determinerStatutApresReception($appro->fresh()),
            ]);

            return $bon;
        });
    }

    public function genererBonReceptionPdf(BonReception $bon)
    {
        $pdf = Pdf::loadView('pdf.bon-reception', [
            'bon' => $bon->load(['demande.chantier', 'receptionneePar']),
        ])->setPaper('A4', 'portrait');

        $nomFichier = 'bon-reception-' . $bon->id . '-' . now()->format('d-m-Y') . '.pdf';

        return $pdf->download($nomFichier);
    }
}
