<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Matiere;
class MatiereDAO {
    private PDO $_db;

    public function __construct(?PDO $pdo = null) {
        $this->_db = $pdo ?? Connexion::getInstance();
    }

    public function getById(int $id): ?Matiere {
        $stmt = $this->_db->prepare("SELECT * FROM matiere WHERE id_mat = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Matiere(
            $data['id_mat'],
            $data['nom_mat'],
            $data['id_promo']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM matiere ORDER BY nom_mat");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Matiere(
                $data['id_mat'],
                $data['nom_mat'],
                $data['id_promo']
            );
        }
        return $list;
    }

    public function insert(Matiere $m): bool {
        $stmt = $this->_db->prepare("INSERT INTO matiere (nom_mat, id_promo) VALUES (:nom, :id_promo)");
        $res = $stmt->execute([
            ':nom' => $m->getNomMatiere(),
            ':id_promo' => $m->getIdPromo()
        ]);

        if ($res) {
            $m->setIdMatiere((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Matiere $m): bool {
        $stmt = $this->_db->prepare("DELETE FROM matiere WHERE id_mat = :id");
        return $stmt->execute([':id' => $m->getIdMatiere()]);
    }

    public function update(Matiere $m): bool {
        $stmt = $this->_db->prepare("UPDATE matiere SET nom_mat = :nom, id_promo = :id_promo WHERE id_mat = :id");
        return $stmt->execute([
            ':nom' => $m->getNomMatiere(),
            ':id_promo' => $m->getIdPromo(),
            ':id' => $m->getIdMatiere()
        ]);
    }

    public function findAllWithPromo(): array
    {
        $sql = "SELECT m.id_mat, m.nom_mat, p.id_promo, p.nom_promo, p.annee, d.nom_dpt 
                FROM matiere m 
                JOIN promotion p ON m.id_promo = p.id_promo
                LEFT JOIN departement d ON p.id_dpt = d.id_dpt
                ORDER BY p.nom_promo, m.nom_mat";

        $stmt = $this->_db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->_db->prepare("DELETE FROM matiere WHERE id_mat = :id");
        return $stmt->execute([':id' => $id]);
    }
}
