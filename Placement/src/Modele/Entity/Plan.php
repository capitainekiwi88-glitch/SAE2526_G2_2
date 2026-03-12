<?php 
namespace App\Modele\Entity;
class Plan{
    private int $_idPlan;
    private string $_donnee;

    public function __construct(int $idPlan, string $donnee){
        $this->setIdPlan($idPlan);
        $this->setDonnee($donnee);
    }

    public function getIdPlan(): int{
        return $this->_idPlan;
    }
    public function setIdPlan(int $val){
        $this->_idPlan = $val;
    }

    public function getDonnee(): string{
        return $this->_donnee;
    }
    public function setDonnee(string $val){
        $this->_donnee = $val;
    }
    public function __toString(){
        return $this->getDonnee();
    }

    public function equals(Plan $autre){
        return $this->getIdPlan() == $autre->getIdPlan();
    }

    public function compareTo(Plan $autre){
        return strcmp($this->getDonnee(), $autre->getDonnee());
    }
}
?>