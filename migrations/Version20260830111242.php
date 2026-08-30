<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260830111242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace the company-name B-tree with a trigram index matching substring search.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        $this->addSql('DROP INDEX idx_review_company_name');
        $this->addSql('CREATE INDEX idx_review_company_name_trgm ON review USING GIN (LOWER(company_name) gin_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_review_company_name_trgm');
        $this->addSql('CREATE INDEX idx_review_company_name ON review (company_name)');
    }
}
