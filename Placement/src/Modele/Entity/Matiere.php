<?php 
namespace App\Modele\Entity;
class Matiere{
    private int $_idMatiere;
    private string $_nomMatiere;

    public function __construct(int $idMatiere, string $nomMatiere){
        $this->setIdMatiere($idMatiere);
        $this->setNomMatiere($nomMatiere);
    }

    public function getIdMatiere(): int{
        return $this->_idMatiere;
    }
    public function setIdMatiere(int $val){
        $this->_idMatiere = $val;
    }

    public function getNomMatiere(): string{
        return $this->_nomMatiere;
    }

    public function setNomMatiere(string $val){
        $this->_nomMatiere = $val;
    }

    public function __toString(){
        return $this->getNomMatiere();
    }
    public function equals(Matiere $autre){
        return $this->getIdMatiere() == $autre->getIdMatiere();
    }

    public function compareTo(Matiere $autre){
        return strcmp($this->getNomMatiere(), $autre->getNomMatiere());
    }
}
?>