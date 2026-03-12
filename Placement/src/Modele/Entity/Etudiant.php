<?php
namespace App\Modele\Entity;
class Etudiant {
  private int $_idEtudiant;
  private string $_nom;
  private string $_prenom;
  private string $_demigroupe;
  private bool $_tiersTemps;
  private bool $_mobReduite;

  public function __construct(int $idEtudiant, string $nom, string $prenom, string $demigroupe, bool $tiersTemps, bool $mobReduite) {
        $this->setIdEtudiant($idEtudiant);
        $this->setNom($nom);
        $this->setPrenom($prenom);
        $this->setDemigroupe($demigroupe);
        $this->setTiersTemps($tiersTemps);
        $this->setMobReduite($mobReduite);
  }

  public function getIdEtudiant() : int { return $this->_idEtudiant; }
    public function setIdEtudiant(int $val) { $this->_idEtudiant = $val; }

    public function getNom() : string { return $this->_nom; }
    public function setNom(string $val) { 
        $this->_nom = strtoupper($val);
    }

    public function getPrenom() : string { return $this->_prenom; }
    public function setPrenom(string $val) { $this->_prenom = $val; }

    public function getDemigroupe() : string { return $this->_demigroupe; }
    public function setDemigroupe(string $val) { $this->_demigroupe = $val; }

    public function getTiersTemps() : bool { return $this->_tiersTemps; }
    public function setTiersTemps(bool $val) { 
        $this->_tiersTemps = $val;
    }

    public function getMobReduite() : bool { return $this->_mobReduite; }
    public function setMobReduite(bool $val) { 
        $this->_mobReduite = $val; 
    }

    public function __toString(){
        return $this->getPrenom() . " " . $this->getNom() . " (" . $this->getDemigroupe() . ")";
    }

    public function equals(Etudiant $autre){
        return $this->getIdEtudiant() == $autre->getIdEtudiant();
    }

    public function compareTo(Etudiant $autre){
        $compNom = strcmp($this->getNom(), $autre->getNom());
        if($compNom != 0) return $compNom;
        return strcmp($this->getPrenom(), $autre->getPrenom());
    }
}
?>