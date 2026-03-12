<?php
namespace App\Modele\Entity;
class Salle{
    private int $_idSalle;
    private string $_nom;
    private int $_capacite;
    private int $_etage;
    private int $_idPlan;
    private int $_idDpt;
    private int $_idBatiment;

    public function __construct(int $idSalle, string $nom, int $capacite, int $etage, int $idPlan, int $idDpt, int $idBatiment){
        $this->setIdSalle($idSalle);
        $this->setNom($nom);
        $this->setCapacite($capacite);
        $this->setEtage($etage);
        $this->setIdPlan($idPlan);
        $this->setIdDpt($idDpt);
        $this->setIdBatiment($idBatiment);
    }

    public function getIdSalle(): int{
        return $this->_idSalle;
    }
    public function setIdSalle(int $val){
        $this->_idSalle = $val;
    }

    public function getNom(): string{
        return $this->_nom;
    }
    public function setNom(string $val){
        $this->_nom = $val;
    }
    public function getCapacite(): int{
        return $this->_capacite;
    }
    public function setCapacite(int $val){
        $this->_capacite = $val;
    }

    public function getEtage(): int{
        return $this->_etage;
    }
    public function setEtage(int $val){
        $this->_etage = $val;
    }
    public function getIdPlan(): int{
        return $this->_idPlan;
    }
    public function setIdPlan(int $val){
        $this->_idPlan = $val;
    }
    public function getIdDpt(): int{
        return $this->_idDpt;
    }
    public function setIdDpt(int $val){
        $this->_idDpt = $val;
    }
    public function getIdBatiment(): int{
        return $this->_idBatiment;
    }
    public function setIdBatiment(int $val){
        $this->_idBatiment = $val;
    }

    public function __toString(){
        return $this->getNom() . " (Capacité: " . $this->getCapacite() . ", Étage: " . $this->getEtage() . ")";
    }

    public function equals(Salle $autre){
        return $this->getIdSalle() == $autre->getIdSalle();
    }

    public function compareTo(Salle $autre){
        $compNom = strcmp($this->getNom(), $autre->getNom());
        if($compNom != 0) return $compNom;
        return $this->getCapacite() <=> $autre->getCapacite();
    }
} 
?>