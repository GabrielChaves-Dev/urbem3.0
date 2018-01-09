<?php

namespace Application\Migrations;

use Urbem\CoreBundle\Helper\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170717155510 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("INSERT INTO administracao.rota (cod_rota,descricao_rota, traducao_rota, rota_superior) VALUES ( nextval('administracao.rota_cod_rota_seq'),'urbem_tributario_arrecadacao_documento_emissao_create', 'Emissão de Documentos', 'tributario_arrecadacao_documento_home');");
        $this->addSql("INSERT INTO administracao.rota (cod_rota,descricao_rota, traducao_rota, rota_superior) VALUES ( nextval('administracao.rota_cod_rota_seq'),'urbem_tributario_arrecadacao_documento_emissao_download', 'Documentos para Download ', 'urbem_tributario_arrecadacao_documento_emissao_create');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}