<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Placement;
use App\Modele\Entity\Etudiant;
use App\Modele\Entity\Devoir;
class PlacementDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function getByEtudiant(Etudiant $e): ?Placement {
        $stmt = $this->_db->prepare("SELECT * FROM placement WHERE id_etudiant = :id_etudiant");
        $stmt->execute([':id_etudiant' => $e->getIdEtudiant()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) return null;
        $devoir = (new DevoirDAO())->getById($data['id_devoir']);
        $salle = (new SalleDAO())->getById($data['id_salle']);
        return new Placement(
            $e,
            $devoir,
            $salle,
            $data['place_x'],
            $data['place_y']
        );
    }
    public function getByDevoir(Devoir $d): array {
        $list = [];
        $stmt = $this->_db->prepare("SELECT * FROM placement WHERE id_devoir = :id_devoir");
        $stmt->execute([':id_devoir' => $d->getIdDevoir()]);
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $etudiant = (new EtudiantDAO())->getById($data['id_etudiant']);
            $salle = (new SalleDAO())->getById($data['id_salle']);
            if ($etudiant) {
                $list[] = new Placement(
                    $etudiant,
                    $d,
                    $salle,
                    $data['place_x'],
                    $data['place_y']
                );
            }
        }
        return $list;
    }

    public function insert(int $idEtudiant, int $idDevoir, int $idSalle, int $placeX, int $placeY): bool {
        $stmt = $this->_db->prepare(
            "INSERT INTO placement (id_etudiant, id_devoir, id_salle, place_x, place_y)
             VALUES (:etudiant, :devoir, :salle, :x, :y)"
        );
        return $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':devoir' => $idDevoir,
            ':salle' => $idSalle,
            ':x' => $placeX,
            ':y' => $placeY,
        ]);
    }

    public function deleteByDevoir(int $idDevoir): bool {
        $stmt = $this->_db->prepare("DELETE FROM placement WHERE id_devoir = :id");
        return $stmt->execute([':id' => $idDevoir]);
    }
}
