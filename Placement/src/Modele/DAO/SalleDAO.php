<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Salle;
class SalleDAO {
    private PDO $_db;

    public function __construct(?PDO $pdo = null) {
        $this->_db = $pdo ?? Connexion::getInstance();
    }

    public function getById(int $id): ?Salle {
        $stmt = $this->_db->prepare("SELECT * FROM salle WHERE id_salle = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Salle(
            $data['id_salle'],
            $data['nom_salle'],
            $data['capacite'],
            $data['etage'],
            $data['id_plan'],
            $data['id_dpt'],
            $data['id_bat']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM salle ORDER BY nom_salle");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Salle(
                $data['id_salle'],
                $data['nom_salle'],
                $data['capacite'],
                $data['etage'],
                $data['id_plan'],
                $data['id_dpt'],
                $data['id_bat']
            );
        }
        return $list;
    }

    public function insert(Salle $s): bool {
        $stmt = $this->_db->prepare("INSERT INTO salle (nom_salle, capacite, etage, id_plan, id_dpt, id_bat) VALUES (:nom, :capacite, :etage, :id_plan, :id_dpt, :id_bat)");
        $res = $stmt->execute([
            ':nom' => $s->getNom(),
            ':capacite' => $s->getCapacite(),
            ':etage' => $s->getEtage(),
            ':id_plan' => $s->getIdPlan(),
            ':id_dpt' => $s->getIdDpt(),
            ':id_bat' => $s->getIdBatiment()
        ]);

        if ($res) {
            $s->setIdSalle((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Salle $s): bool {
        $stmt = $this->_db->prepare("DELETE FROM salle WHERE id_salle = :id");
        return $stmt->execute([':id' => $s->getIdSalle()]);
    }

    public function update(Salle $s): bool {
        $stmt = $this->_db->prepare("UPDATE salle SET nom_salle = :nom, capacite = :capacite, etage = :etage, id_plan = :id_plan, id_dpt = :id_dpt, id_bat = :id_bat WHERE id_salle = :id");
        return $stmt->execute([
            ':nom' => $s->getNom(),
            ':capacite' => $s->getCapacite(),
            ':etage' => $s->getEtage(),
            ':id_plan' => $s->getIdPlan(),
            ':id_dpt' => $s->getIdDpt(),
            ':id_bat' => $s->getIdBatiment(),
            ':id' => $s->getIdSalle()
        ]);
    }
}
