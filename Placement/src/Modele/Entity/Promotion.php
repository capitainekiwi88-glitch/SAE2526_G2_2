<?php 
namespace App\Modele\Entity;
class Promotion{
    private int $_idPromo;
    private string $_nomPromo;
    private string $_annee;
    private int $_idDpt;
    public function __construct(int $idPromo, string $nomPromo, string $annee, int $idDpt){
        $this->setIdPromo($idPromo);
        $this->setNomPromo($nomPromo);
        $this->setAnnee($annee);
        $this->setIdDpt($idDpt);
    }

    public function getIdPromo(): int{
        return $this->_idPromo;
    }
    public function setIdPromo(int $val){
        $this->_idPromo = $val;
    }

    public function getNomPromo(): string{
        return $this->_nomPromo;
    }
    public function setNomPromo(string $val){
        $this->_nomPromo = $val;
    }
    public function __toString(){
        return $this->getNomPromo();
    }
    public function getAnnee(): string{
        return $this->_annee;
    }
    public function setAnnee(string $val){
        $this->_annee = $val;
    }
    public function getIdDpt(): int{
        return $this->_idDpt;
    }
    public function setIdDpt(int $val){
        $this->_idDpt = $val;
    }
    public function equals(Promotion $autre){
        return $this->getIdPromo() == $autre->getIdPromo();
    }

    public function compareTo(Promotion $autre){
        return strcmp($this->getNomPromo(), $autre->getNomPromo());
    }
}
?>