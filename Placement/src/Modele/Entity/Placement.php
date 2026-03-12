<?php 
namespace App\Modele\Entity;
class Placement {
    private Etudiant $_etudiant;
    private Devoir $_devoir;
    private Salle $_salle;
    private int $_placeX;
    private int $_placeY;

    public function __construct(Etudiant $e, Devoir $d, Salle $s, int $x, int $y) {
        $this->setEtudiant($e);
        $this->setDevoir($d);
        $this->setSalle($s);
        $this->setPlaceX($x);
        $this->setPlaceY($y);
    }

    public function getEtudiant(): Etudiant {
        return $this->_etudiant;
    }
    public function setEtudiant(Etudiant $etudiant): void {
        $this->_etudiant = $etudiant;
    }
    public function getDevoir(): Devoir {
        return $this->_devoir;
    }
    public function setDevoir(Devoir $devoir): void {
        $this->_devoir = $devoir;
    }
    public function getSalle(): Salle {
        return $this->_salle;
    }
    public function setSalle(Salle $salle): void {
        $this->_salle = $salle;
    }
    public function getPlaceX(): int {
        return $this->_placeX;
    }
    public function setPlaceX(int $placeX): void {
        $this->_placeX = $placeX;
    }
    public function getPlaceY(): int {
        return $this->_placeY;
    }
    public function setPlaceY(int $placeY): void {
        $this->_placeY = $placeY;
    }
    public function __toString(){
        return "Etudiant: " . $this->getEtudiant() . ", Devoir: " . $this->getDevoir() . ", Salle: " . $this->getSalle() . ", PlaceX: " . $this->getPlaceX() . ", PlaceY: " . $this->getPlaceY();
    }
    public function equals(Placement $autre){
        return $this->getEtudiant()->equals($autre->getEtudiant()) && $this->getDevoir()->equals($autre->getDevoir()) && $this->getSalle()->equals($autre->getSalle());
    }
    public function compareTo(Placement $autre){
        return strcmp($this->__toString(), $autre->__toString());
    }
}
?>