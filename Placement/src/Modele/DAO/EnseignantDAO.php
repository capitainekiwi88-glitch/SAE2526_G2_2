<?php
namespace App\Modele\DAO;

use App\Modele\Entity\Enseignant;
use PDO;

class EnseignantDAO {
    private PDO $_db;

    public function __construct(?PDO $pdo = null) {
        $this->_db = $pdo ?? Connexion::getInstance();
    }

    public function getById(int $id): ?Enseignant {
        $stmt = $this->_db->prepare("SELECT * FROM enseignant WHERE id_ens = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Enseignant(
            $data['id_ens'],
            $data['nom_ens'],
            $data['prenom_ens'],
            $data['sexe'],
            $data['login'],
            $data['admin']
        );
    }

    public function findAll(): array {
        $list = [];
        $res = $this->_db->query("SELECT * FROM enseignant ORDER BY nom_ens");

        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new Enseignant(
                $data['id_ens'],
                $data['nom_ens'],
                $data['prenom_ens'],
                $data['sexe'],
                $data['login'],
                $data['admin']
            );
        }
        return $list;
    }

    public function insert(Enseignant $e, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->_db->prepare("INSERT INTO enseignant (nom_ens, prenom_ens, sexe, login, admin, pass) VALUES (:nom, :prenom, :sexe, :login, :admin, :password)");
        $res = $stmt->execute([
            ':nom' => $e->getNom(),
            ':prenom' => $e->getPrenom(),
            ':sexe' => $e->getSexe(),
            ':login' => $e->getLogin(),
            ':admin' => $e->getAdmin(),
            ':password' => $hash
        ]);

        if ($res) {
            $e->setIdEnseignant((int)$this->_db->lastInsertId());
        }
        return $res;
    }
    public function findByLogin(string $login): ?array {
        $stmt = $this->_db->prepare("SELECT * FROM enseignant WHERE login = :login");
        $stmt->execute([':login' => $login]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ?: null;
    }

    public function getEnseignantByLogin(string $login): ?Enseignant {
        $data = $this->findByLogin($login);
        if (!$data) return null;

        return new Enseignant(
            $data['id_ens'],
            $data['nom_ens'],
            $data['prenom_ens'],
            $data['sexe'],
            $data['login'],
            $data['admin']
        );
    }

    public function verifyPassword(string $login, string $password): bool {
        $data = $this->findByLogin($login);
        if (!$data || !isset($data['pass'])) return false;

        $stored = $data['pass'];

        // Bcrypt hash
        if (str_starts_with($stored, '$2y$')) {
            return password_verify($password, $stored);
        }

        // Legacy: plain text or md5
        if ($password === $stored || md5($password) === $stored) {
            // Migrate to bcrypt on successful legacy login
            $this->updatePassword((int)$data['id_ens'], password_hash($password, PASSWORD_DEFAULT));
            return true;
        }

        return false;
    }

    public function updatePassword(int $id, string $newHash): bool {
        $stmt = $this->_db->prepare("UPDATE enseignant SET pass = :pass WHERE id_ens = :id");
        return $stmt->execute([':pass' => $newHash, ':id' => $id]);
    }



    public function delete(Enseignant $d): bool {
        $stmt = $this->_db->prepare("DELETE FROM enseignant WHERE id_ens = :id");
        return $stmt->execute([':id' => $d->getIdEnseignant()]);
    }

    public function update(Enseignant $d): bool {
        $stmt = $this->_db->prepare("UPDATE enseignant SET nom_ens = :nom, prenom_ens = :prenom, sexe = :sexe, login = :login, admin = :admin WHERE id_ens = :id");
        return $stmt->execute([
            ':nom' => $d->getNom(),
            ':prenom' => $d->getPrenom(),
            ':sexe' => $d->getSexe(),
            ':login' => $d->getLogin(),
            ':admin' => $d->getAdmin(),
            ':id'  => $d->getIdEnseignant()
        ]);
    }

    public function deleteById(int $id): bool {
        $stmt = $this->_db->prepare("DELETE FROM enseignant WHERE id_ens = :id");
        return $stmt->execute([':id' => $id]);
    }

}