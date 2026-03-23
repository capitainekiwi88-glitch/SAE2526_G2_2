<?php 
namespace App\Modele\DAO;
use PDO;
use App\Modele\Entity\Groupe;
class GroupeDAO {
    private PDO $_db;

    public function __construct() {
        $this->_db = Connexion::getInstance();
    }

    public function getById(int $id): ?Groupe {
        $stmt = $this->_db->prepare("SELECT * FROM groupe WHERE id_groupe = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Groupe(
            $data['id_groupe'],
            $data['nom_groupe'],
            $data['nb_etud'],
            $data['id_promo']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM groupe ORDER BY nom_groupe");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Groupe(
                $data['id_groupe'],
                $data['nom_groupe'],
                $data['nb_etud'],
                $data['id_promo']
            );
        }
        return $list;
    }

    public function insert(Groupe $g): bool {
        $stmt = $this->_db->prepare("INSERT INTO groupe (nom_groupe, nb_etud, id_promo) VALUES (:nom, :nb_etud, :id_promo)");
        $res = $stmt->execute([
            ':nom' => $g->getNomGroupe(),
            ':nb_etud' => $g->getNbEtudiant(),
            ':id_promo' => $g->getIdPromo()
        ]);

        if ($res) {
            $g->setIdGroupe((int)$this->_db->lastInsertId());
        }
        return $res;
    }

    public function delete(Groupe $g): bool {
        $stmt = $this->_db->prepare("DELETE FROM groupe WHERE id_groupe = :id");
        return $stmt->execute([':id' => $g->getIdGroupe()]);
    }

    public function update(Groupe $g): bool {
        $stmt = $this->_db->prepare("UPDATE groupe SET nom_groupe = :nom, id_promo = :id_promo, nb_etud = :nb_etud WHERE id_groupe = :id");
        return $stmt->execute([
            ':nom' => $g->getNomGroupe(),
            ':id_promo' => $g->getIdPromo(),
            ':nb_etud' => $g->getNbEtudiant(),
            ':id' => $g->getIdGroupe()
        ]);
    }
}
