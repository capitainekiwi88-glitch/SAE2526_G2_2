<?php
namespace App\Modele\DAO;

use App\Modele\Entity\Batiment;
use PDO;

class BatimentDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function getById(int $id): ?Batiment {
        $stmt = $this->_db->prepare("SELECT * FROM batiment WHERE id_bat = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Batiment(
            $data['id_bat'],
            $data['nom_bat'],
            $data['ad_bat']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM batiment ORDER BY nom_bat");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Batiment(
                $data['id_bat'],
                $data['nom_bat'],
                $data['ad_bat']
            );
        }
        return $list;
    }

    public function insert(Batiment $b): bool {
        $stmt = $this->_db->prepare("INSERT INTO batiment (nom_bat, ad_bat) VALUES (:nom, :ad)");
        $res = $stmt->execute([
            ':nom' => $b->getNom(),
            ':ad'  => $b->getAdresse()
        ]);
        
        if ($res) {
            $b->setIdBatiment((int)$this->_db->lastInsertId());
        }
        return $res;
    }
}