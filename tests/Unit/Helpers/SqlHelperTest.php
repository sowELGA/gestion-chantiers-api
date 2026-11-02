<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SqlHelper;
use App\Models\Ouvrier;
use Tests\TestCase;

class SqlHelperTest extends TestCase
{
    public function test_ordre_personnalise_remplace_field_mysql(): void
    {
        $sql = SqlHelper::ordrePersonnalise('priorite', ['urgent', 'normal']);

        $this->assertStringNotContainsString('FIELD', $sql);
        $this->assertStringContainsString('CASE', $sql);
        $this->assertStringContainsString("'urgent'", $sql);
        $this->assertStringContainsString("'normal'", $sql);
        $this->assertLessThan(
            strpos($sql, "'normal'"),
            strpos($sql, "'urgent'"),
        );
    }

    public function test_operateur_contient_reste_like_sous_sqlite(): void
    {
        $this->assertSame('like', SqlHelper::operateurContient(Ouvrier::query()));
    }
}
