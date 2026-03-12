<?php
namespace App\Modele\Entity;
use DateTime;
class Devoir
{
    private int $_idDevoir;
    private string $_nom;
    private DateTime $_date;
    private DateTime $_heure;
    private DateTime $_duree;

    public function __construct(int $idDevoir, string $nom, DateTime $date, DateTime $heure, DateTime $duree)
    {
        $this->setIdDevoir($idDevoir);
        $this->setNom($nom);
        $this->setDate($date);
        $this->setHeure($heure);
        $this->setDuree($duree);
    }

    public function getIdDevoir(): int{
        return $this->_idDevoir;
    }
    public function setIdDevoir(int $val){
        $this->_idDevoir = $val;
    }

    public function getNom(): string{
        return $this->_nom;
    }
    public function setNom(string $val){
        $this->_nom = $val;
    }

    public function getDate(): DateTime{
        return $this->_date;
    }
    public function setDate(DateTime $val){
        $this->_date = $val;
    }

    public function getHeure(): DateTime
    {
        return $this->_heure;
    }
    public function setHeure(DateTime $val)
    {
        $this->_heure = $val;
    }

    public function getDuree(): DateTime
    {
        return $this->_duree;
    }
    public function setDuree(DateTime $val)
    {
        $this->_duree = $val;
    }

    public function __toString(){
        return $this->getNom() . " le " . $this->getDate()->format('d/m/Y') . " à " . $this->getHeure()->format('H:i') . " pour une durée de " . $this->getDuree()->format('H:i');
    }

    public function equals(Devoir $autre){
        return $this->getIdDevoir() == $autre->getIdDevoir();
    }

    public function compareTo(Devoir $autre){
        $compNom = strcmp($this->getNom(), $autre->getNom());
        if($compNom != 0) return $compNom;
        return $this->getDate() <=> $autre->getDate();
    }
}
?>