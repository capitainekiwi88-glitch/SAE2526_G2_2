<?php
namespace App\Modele\Entity;
class Batiment{
    private int $_idBatiment;
    private string $_nom;
    private string $_adresse;

    public function __construct(int $idBatiment, string $nom, string $adresse){
        $this->setIdBatiment($idBatiment);
        $this->setNom($nom);
        $this->setAdresse($adresse);
    }

    public function getIdBatiment(): int{
        return $this->_idBatiment;
    }
    public function setIdBatiment(int $val){
        $this->_idBatiment = $val;
    }

    public function getNom(): string{
        return $this->_nom;
    }
    public function setNom(string $val){
        $this->_nom = $val;
    }
    public function getAdresse(): string{
        return $this->_adresse;
    }
    public function setAdresse(string $val){
        $this->_adresse = $val;
    }
    public function __toString(){
        return $this->getNom() . " - " . $this->getAdresse();
    }

    public function equals(Batiment $autre){
        return $this->getIdBatiment() == $autre->getIdBatiment();
    }

    public function compareTo(Batiment $autre){
        $compNom = strcmp($this->getNom(), $autre->getNom());
        if($compNom != 0) return $compNom;
        return $this->getAdresse() <=> $autre->getAdresse();
    }
}
?>