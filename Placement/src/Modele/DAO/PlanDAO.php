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
            $list[] = new Plan(
                $data['id_plan'],
                $data['donnee'],
            );
        }
        return $list;
    }

    public function insert(Plan $p): bool {
        $stmt = $this->_db->prepare("INSERT INTO plan (donnee) VALUES (:donnee)");
        $res = $stmt->execute([
            ':donnee' => $p->getDonnee()
        ]);

        if ($res) {
            $p->setIdPlan((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Plan $p): bool {
        $stmt = $this->_db->prepare("DELETE FROM plan WHERE id_plan = :id");
        return $stmt->execute([':id' => $p->getIdPlan()]);
    }

    public function update(Plan $p): bool {
        $stmt = $this->_db->prepare("UPDATE plan SET donnee = :donnee WHERE id_plan = :id");
        return $stmt->execute([
            ':donnee' => $p->getDonnee(),
            ':id'     => $p->getIdPlan()
        ]);
    }

}