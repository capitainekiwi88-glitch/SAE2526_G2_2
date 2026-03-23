<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Plan;
class PlanDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function getById(int $id): ?Plan {
        $stmt = $this->_db->prepare("SELECT * FROM plan WHERE id_plan = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Plan(
            $data['id_plan'],
            $data['donnee'],
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM plan");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Departement(
                $data['id_dpt'],
                $data['nom_dpt'],
            );
        }
        return $list;
    }

    public function insert(Departement $d): bool {
        $stmt = $this->_db->prepare("INSERT INTO departement (nom_dpt) VALUES (:nom)");
        $res = $stmt->execute([
            ':nom' => $d->getNom()
        ]);

        if ($res) {
            $d->setIdDpt((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Departement $d): bool {
        $stmt = $this->_db->prepare("DELETE FROM departement WHERE id_dpt = :id");
        return $stmt->execute([':id' => $d->getIdDpt()]);
    }

    public function update(Departement $d): bool {
        $stmt = $this->_db->prepare("UPDATE departement SET nom_dpt = :nom WHERE id_dpt = :id");
        return $stmt->execute([
            ':nom' => $d->getNom(),
            ':id'  => $d->getIdDpt()
        ]);
    }

}