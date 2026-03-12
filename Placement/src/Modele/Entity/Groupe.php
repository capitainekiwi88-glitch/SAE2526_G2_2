<?php 
namespace App\Modele\Entity;
class Groupe{
    private int $_idGroupe;
    private string $_nomGroupe;
    private int $_nbEtudiant;
    private int $_idPromo;
    public function __construct(int $idGroupe, string $nomGroupe, int $nbEtudiant, int $idPromo) {
        $this->setIdGroupe($idGroupe);
        $this->setNomGroupe($nomGroupe);
        $this->setNbEtudiant($nbEtudiant);
        $this->setIdPromo($idPromo);
    }

    public function getIdGroupe(): int {
        return $this->_idGroupe;
    }
    public function setIdGroupe(int $idGroupe): void {
        $this->_idGroupe = $idGroupe;
    }

    public function getNomGroupe(): string {
        return $this->_nomGroupe;
    }

    public function setNomGroupe(string $nomGroupe): void {
        $this->_nomGroupe = $nomGroupe;
    }

    public function getNbEtudiant(): int {
        return $this->_nbEtudiant;
    }

    public function setNbEtudiant(int $nbEtudiant): void {
        $this->_nbEtudiant = $nbEtudiant;
    }

    public function getIdPromo(): int {
        return $this->_idPromo;
    }
    public function setIdPromo(int $idPromo): void {
        $this->_idPromo = $idPromo;
    }
    public function __toString(){
        return $this->getNomGroupe();
    }

    public function equals(Groupe $autre){
        return $this->getIdGroupe() == $autre->getIdGroupe();
    }

    public function compareTo(Groupe $autre){
        return strcmp($this->getNomGroupe(), $autre->getNomGroupe());
    }
}

?>