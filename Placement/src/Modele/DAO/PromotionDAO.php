<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Promotion;
class PromotionDAO {
    private PDO $_db;

    public function __construct(?PDO $pdo = null) {
        $this->_db = $pdo ?? Connexion::getInstance();
    }

    public function getById(int $id): ?Promotion {
        $stmt = $this->_db->prepare("SELECT * FROM promotion WHERE id_promo = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Promotion(
            $data['id_promo'],
            $data['nom_promo'],
            $data['annee'],
            $data['id_dpt']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM promotion ORDER BY nom_promo");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Promotion(
                $data['id_promo'],
                $data['nom_promo'],
                $data['annee'],
                $data['id_dpt']
            );
        }
        return $list;
    }

    public function insert(Promotion $p): bool {
        $stmt = $this->_db->prepare("INSERT INTO promotion (nom_promo, annee, id_dpt) VALUES (:nom, :annee, :id_dpt)");
        $res = $stmt->execute([
            ':nom' => $p->getNomPromo(),
            ':annee' => $p->getAnnee(),
            ':id_dpt' => $p->getIdDpt()
        ]);

        if ($res) {
            $p->setIdPromo((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Promotion $p): bool {
        $stmt = $this->_db->prepare("DELETE FROM promotion WHERE id_promo = :id");
        return $stmt->execute([':id' => $p->getIdPromo()]);
    }

    public function update(Promotion $p): bool {
        $stmt = $this->_db->prepare("UPDATE promotion SET nom_promo = :nom, annee = :annee, id_dpt = :id_dpt WHERE id_promo = :id");
        return $stmt->execute([
            ':nom' => $p->getNomPromo(),
            ':annee' => $p->getAnnee(),
            ':id_dpt'=> $p->getIdDpt(),
            ':id'=> $p->getIdPromo()
        ]);
    }

}