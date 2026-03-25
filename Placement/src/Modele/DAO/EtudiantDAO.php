<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Etudiant;
class EtudiantDAO {
    private PDO $_db;

    public function __construct(?PDO $pdo = null) {
        $this->_db = $pdo ?? Connexion::getInstance();
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

    public function getStudentsForSelection(int $promoId, int $groupId = 0): array {
        if ($groupId === 0) {
            $stmt = $this->_db->prepare(
                'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant, e.id_groupe
                 FROM etudiant e
                 JOIN groupe g ON g.id_groupe = e.id_groupe
                 WHERE g.id_promo = :promo
                 ORDER BY e.nom_etudiant, e.prenom_etudiant'
            );
            $stmt->execute(['promo' => $promoId]);
        } else {
            $stmt = $this->_db->prepare(
                'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant, e.id_groupe
                 FROM etudiant e
                 WHERE e.id_groupe = :groupe
                 ORDER BY e.nom_etudiant, e.prenom_etudiant'
            );
            $stmt->execute(['groupe' => $groupId]);
        }

        $students = [];
        foreach ($stmt->fetchAll() as $row) {
            $students[] = [
                'id' => (string) $row['id_etudiant'],
                'promo_id' => $promoId,
                'group_id' => (int) $row['id_groupe'],
                'last_name' => (string) $row['nom_etudiant'],
                'first_name' => (string) $row['prenom_etudiant'],
                'display_name' => $row['nom_etudiant'] . ' ' . $row['prenom_etudiant'],
            ];
        }

        return $students;
    }

    public function getStudentsForPromotion(int $promoId): array {
        $stmt = $this->_db->prepare(
            'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant, e.id_groupe
             FROM etudiant e
             JOIN groupe g ON g.id_groupe = e.id_groupe
             WHERE g.id_promo = :promo
             ORDER BY e.nom_etudiant, e.prenom_etudiant'
        );
        $stmt->execute(['promo' => $promoId]);

        $students = [];
        foreach ($stmt->fetchAll() as $row) {
            $students[] = [
                'id' => (string) $row['id_etudiant'],
                'promo_id' => $promoId,
                'group_id' => (int) $row['id_groupe'],
                'last_name' => (string) $row['nom_etudiant'],
                'first_name' => (string) $row['prenom_etudiant'],
                'display_name' => $row['nom_etudiant'] . ' ' . $row['prenom_etudiant'],
            ];
        }

        return $students;
    }

}