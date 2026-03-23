<?php
namespace App\Modele\DAO;

use App\Modele\Entity\Devoir;
use PDO;

class DevoirDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function getById(int $id): ?Devoir {
        $stmt = $this->_db->prepare("SELECT * FROM devoir WHERE id_devoir = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Devoir(
            $data['id_devoir'],
            $data['nom_devoir'],
            $data['date_devoir'],
            $data['heure_devoir'],
            $data['duree_devoir']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM devoir ORDER BY nom_devoir");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Devoir(
                $data['id_devoir'],
                $data['nom_devoir'],
                $data['date_devoir'],
                $data['heure_devoir'],
                $data['duree_devoir']
            );
        }
        return $list;
    }

    public function insert(Devoir $d): bool {
        $stmt = $this->_db->prepare("INSERT INTO devoir (nom_devoir, date_devoir, heure_devoir, duree_devoir) VALUES (:nom, :date, :heure, :duree)");
        $res = $stmt->execute([
            ':nom' => $d->getNom(),
            ':date' => $d->getDate(),
            ':heure' => $d->getHeure(),
            ':duree' => $d->getDuree()
        ]);

        if ($res) {
            $d->setIdDevoir((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Devoir $d): bool {
        $stmt = $this->_db->prepare("DELETE FROM devoir WHERE id_devoir = :id");
        return $stmt->execute([':id' => $d->getIdDevoir()]);
    }

    public function update(Devoir $d): bool {
        $stmt = $this->_db->prepare("UPDATE devoir SET nom_devoir = :nom, date_devoir = :date, heure_devoir = :heure, duree_devoir = :duree WHERE id_devoir = :id");
        return $stmt->execute([
            ':nom' => $d->getNom(),
            ':id'  => $d->getIdDevoir()
        ]);
    }

}