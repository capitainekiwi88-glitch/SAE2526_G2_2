<?php
namespace App\Modele\DAO;
use App\Modele\Entity\DevoirGroupe;
use PDO;

class DevoirGroupeDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function findAll(): array {
    $list = [];
    $res = $this->_db->query("SELECT * FROM devoir_groupe");

    while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
        $list[] = new DevoirGroupe(
            $data['id_salle'],
            $data['id_devoir'],
            $data['id_groupe'],
            $data['id_mat']
        );
    }
        return $list;
    }
    public function findByGroupe(int $idGroupe): array {
        $stmt = $this->_db->prepare("SELECT * FROM devoir_groupe WHERE id_groupe = :id");
        $stmt->execute([':id' => $idGroupe]);

        $list = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new DevoirGroupe(
                $data['id_salle'],
                $data['id_devoir'],
                $data['id_groupe'],
                $data['id_mat']
            );
        }
        return $list;
    }
    public function findByDevoir(int $idDevoir): array {
        $stmt = $this->_db->prepare("SELECT * FROM devoir_groupe WHERE id_devoir = :id");
        $stmt->execute([':id' => $idDevoir]);

        $list = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new DevoirGroupe(
                $data['id_salle'],
                $data['id_devoir'],
                $data['id_groupe'],
                $data['id_mat']
            );
        }
        return $list;
    }
    public function insert(DevoirGroupe $dg): bool {
        $stmt = $this->_db->prepare("
            INSERT INTO devoir_groupe (id_salle, id_devoir, id_groupe, id_mat)
            VALUES (:salle, :devoir, :groupe, :mat)
        ");

        return $stmt->execute([
            ':salle' => $dg->getIdSalle(),
            ':devoir' => $dg->getIdDevoir(),
            ':groupe' => $dg->getIdGroupe(),
            ':mat' => $dg->getIdMat()
        ]);
    }
    public function deleteByDevoir(int $idDevoir): bool {
        $stmt = $this->_db->prepare("DELETE FROM devoir_groupe WHERE id_devoir = :id");
        return $stmt->execute([':id' => $idDevoir]);
    }

    public function delete(DevoirGroupe $dg): bool {
        $stmt = $this->_db->prepare("
            DELETE FROM devoir_groupe 
            WHERE id_salle = :salle 
            AND id_devoir = :devoir 
            AND id_groupe = :groupe
        ");

        return $stmt->execute([
            ':salle' => $dg->getIdSalle(),
            ':devoir' => $dg->getIdDevoir(),
            ':groupe' => $dg->getIdGroupe()
        ]);
    }
}
?>