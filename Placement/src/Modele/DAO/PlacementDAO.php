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
}
