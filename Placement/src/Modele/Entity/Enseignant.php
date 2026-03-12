<?php 
namespace App\Modele\Entity;
class Enseignant{
    private int $_idEnseignant;
    private string $_nom;
    private string $_prenom;
    private string $_sexe;
    private string $_login;
    private bool $_admin;

    // password missing voluntarily ...

    public function __construct(int $idEnseignant, string $nom, string $prenom, string $sexe, string $login, bool $admin){
        $this->setIdEnseignant($idEnseignant);
        $this->setNom($nom);
        $this->setPrenom($prenom);
        $this->setSexe($sexe);
        $this->setLogin($login);
        $this->setAdmin($admin);
    }

    public function getIdEnseignant(): int{
        return $this->_idEnseignant;
    }
    public function setIdEnseignant(int $val){
        $this->_idEnseignant = $val;
    }

    public function getNom(): string{
        return $this->_nom;
    }
    public function setNom(string $val){
        $this->_nom = $val;
    }
    public function getPrenom(): string{
        return $this->_prenom;
    }
    public function setPrenom(string $val){
        $this->_prenom = $val;
    }
    public function getSexe(): string{
        return $this->_sexe;
    }
    public function setSexe(string $val){
        $this->_sexe = $val;
    }
    public function getLogin(): string{
        return $this->_login;
    }

    public function setLogin(string $val){
        $this->_login = $val;
    }
    public function getAdmin(): bool{
        return $this->_admin;
    }
    public function setAdmin(bool $val){
        $this->_admin = $val;
    }
    public function __toString(){
        return $this->getNom();
    }

    public function equals(Enseignant $autre){
        return $this->getIdEnseignant() == $autre->getIdEnseignant();
    }

    public function compareTo(Enseignant $autre){
        $compNom = strcmp($this->getNom(), $autre->getNom());
        if($compNom != 0) return $compNom;
        return strcmp($this->getPrenom(), $autre->getPrenom());
    }
}
?>