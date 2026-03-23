<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Etudiant;
class EtudiantDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function getById(int $id): ?Etudiant {
        $stmt = $this->_db->prepare("SELECT * FROM etudiant WHERE id_etudiant = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Etudiant(
            $data['id_etudiant'],
            $data['nom_etudiant'],
            $data['prenom_etudiant'],
            $data['demigr'],
            $data['tiers_temps'],
            $data['mob_reduite']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM etudiant ORDER BY nom_etudiant");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Etudiant(
                $data['id_etudiant'],
                $data['nom_etudiant'],
                $data['prenom_etudiant'],
                $data['demigr'],
                $data['tiers_temps'],
                $data['mob_reduite']
            );
        }
        return $list;
    }

    public function insert(Etudiant $e): bool {
        $stmt = $this->_db->prepare("INSERT INTO etudiant (nom_etudiant, prenom_etudiant, demigr, tiers_temps, mob_reduite) VALUES (:nom, :prenom, :demigr, :tiers_temps, :mob_reduite)");
        $res = $stmt->execute([
            ':nom' => $e->getNom(),
            ':prenom' => $e->getPrenom(),
            ':demigr' => $e->getDemigroupe(),
            ':tiers_temps' => $e->getTiersTemps(),
            ':mob_reduite' => $e->getMobReduite()
        ]);

        if ($res) {
            $e->setIdEtudiant((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Etudiant $e): bool {
        $stmt = $this->_db->prepare("DELETE FROM etudiant WHERE id_etudiant = :id");
        return $stmt->execute([':id' => $e->getIdEtudiant()]);
    }

    public function update(Etudiant $e): bool {
        $stmt = $this->_db->prepare("UPDATE etudiant SET nom_etudiant = :nom, prenom_etudiant = :prenom, demigr = :demigr, tiers_temps = :tiers_temps, mob_reduite = :mob_reduite WHERE id_etudiant = :id");
        return $stmt->execute([
            ':nom' => $e->getNom(),
            ':prenom' => $e->getPrenom(),
            ':demigr' => $e->getDemigroupe(),
            ':tiers_temps' => $e->getTiersTemps(),
            ':mob_reduite' => $e->getMobReduite(),
            ':id'  => $e->getIdEtudiant()
        ]);
    }

}