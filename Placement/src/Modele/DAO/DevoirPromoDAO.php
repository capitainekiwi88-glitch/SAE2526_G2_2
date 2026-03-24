<?php
namespace App\Modele\DAO;
use App\Modele\Entity\DevoirPromo;
use PDO;

class DevoirPromoDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function findAll(): array {
    $list = [];
    $res = $this->_db->query("SELECT * FROM devoir_promo");

    while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
        $list[] = new DevoirPromo(
            $data['id_salle'],
            $data['id_devoir'],
            $data['id_promo'],
            $data['id_mat']
        );
    }
        return $list;
    }
    public function findByPromo(int $idPromo): array {
        $stmt = $this->_db->prepare("SELECT * FROM devoir_promo WHERE id_promo = :id");
        $stmt->execute([':id' => $idPromo]);

        $list = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new DevoirPromo(
                $data['id_salle'],
                $data['id_devoir'],
                $data['id_promo'],
                $data['id_mat']
            );
        }
        return $list;
    }
    public function findByDevoir(int $idDevoir): array {
        $stmt = $this->_db->prepare("SELECT * FROM devoir_promo WHERE id_devoir = :id");
        $stmt->execute([':id' => $idDevoir]);

        $list = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new DevoirPromo(
                $data['id_salle'],
                $data['id_devoir'],
                $data['id_promo'],
                $data['id_mat']
            );
        }
        return $list;
    }
    public function insert(DevoirPromo $dg): bool {
        $stmt = $this->_db->prepare("
            INSERT INTO devoir_promo (id_salle, id_devoir, id_promo, id_mat)
            VALUES (:salle, :devoir, :promo, :mat)
        ");

        return $stmt->execute([
            ':salle' => $dg->getIdSalle(),
            ':devoir' => $dg->getIdDevoir(),
            ':promo' => $dg->getIdPromo(),
            ':mat' => $dg->getIdMat()
        ]);
    }
    public function delete(DevoirPromo $dg): bool {
        $stmt = $this->_db->prepare("
            DELETE FROM devoir_promo 
            WHERE id_salle = :salle 
            AND id_devoir = :devoir 
            AND id_promo = :promo
        ");

        return $stmt->execute([
            ':salle' => $dg->getIdSalle(),
            ':devoir' => $dg->getIdDevoir(),
            ':promo' => $dg->getIdPromo()
        ]);
    }
}
?>