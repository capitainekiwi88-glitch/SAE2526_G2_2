<?php
namespace App\Modele\Entity;

class DevoirGroupe {
    private int $idSalle;
    private int $idDevoir;
    private int $idGroupe;
    private int $idMat;

    public function __construct($idSalle, $idDevoir, $idGroupe, $idMat) {
        $this->idSalle = $idSalle;
        $this->idDevoir = $idDevoir;
        $this->idGroupe = $idGroupe;
        $this->idMat = $idMat;
    }

    public function getIdSalle():int{
        return $this->idSalle;
    }
    public function getIdDevoir():int{
        return $this->idDevoir;
    }
    public function getIdGroupe():int{
        return $this->idGroupe;
    }
    public function getIdMat():int{
        return $this->idMat;
    }
}
?>