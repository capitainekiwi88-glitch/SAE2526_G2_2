<?php 
namespace App\Modele\Entity;
class Departement{
    private int $_idDpt;
    private string $_nom;

    public function __construct(int $idDpt, string $nom){
        $this->setIdDpt($idDpt);
        $this->setNom($nom);
    }

    public function getIdDpt(): int{
        return $this->_idDpt;
    }
    public function setIdDpt(int $val){
        $this->_idDpt = $val;
    }

    public function getNom(): string{
        return $this->_nom;
    }
    public function setNom(string $val){
        $this->_nom = $val;
    }
    public function __toString(){
        return $this->getNom();
    }

    public function equals(Departement $autre){
        return $this->getIdDpt() == $autre->getIdDpt();
    }

    public function compareTo(Departement $autre){
        return strcmp($this->getNom(), $autre->getNom());
    }
}
?>