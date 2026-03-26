<?php 
namespace App\Modele\DAO;

use PDO;
use App\Modele\Entity\Enseignement;

class EnseignementDAO {
    private PDO $_db;

    public function __construct(?PDO $pdo = null) {
        $this->_db = $pdo ?? Connexion::getInstance();
    }

    public function insert(Enseignement $e): bool {
        $stmt = $this->_db->prepare("INSERT INTO enseigne (id_ens, id_mat) VALUES (:id_ens, :id_mat)");
        return $stmt->execute([
            ':id_ens' => $e->getIdEns(),
            ':id_mat' => $e->getIdMat()
        ]);
    }

    public function delete(int $idEns, int $idMat): bool {
        $stmt = $this->_db->prepare("DELETE FROM enseigne WHERE id_ens = :id_ens AND id_mat = :id_mat");
        return $stmt->execute([
            ':id_ens' => $idEns,
            ':id_mat' => $idMat
        ]);
    }

    public function update(int $oldIdEns, int $oldIdMat, Enseignement $newE): bool {
        $stmt = $this->_db->prepare("UPDATE enseigne SET id_ens = :new_ens, id_mat = :new_mat WHERE id_ens = :old_ens AND id_mat = :old_mat");
        return $stmt->execute([
            ':new_ens' => $newE->getIdEns(),
            ':new_mat' => $newE->getIdMat(),
            ':old_ens' => $oldIdEns,
            ':old_mat' => $oldIdMat
        ]);
    }

    public function findAllWithDetails(): array {
        $sql = "SELECT e.id_ens, e.id_mat, ens.nom_ens, ens.prenom_ens, m.nom_mat, p.nom_promo, p.annee, d.nom_dpt 
                FROM enseigne e
                JOIN enseignant ens ON e.id_ens = ens.id_ens
                JOIN matiere m ON e.id_mat = m.id_mat
                JOIN promotion p ON m.id_promo = p.id_promo
                LEFT JOIN departement d ON p.id_dpt = d.id_dpt
                ORDER BY ens.nom_ens, m.nom_mat";
        
        $stmt = $this->_db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}