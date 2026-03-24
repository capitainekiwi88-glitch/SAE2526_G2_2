<?php
namespace App\Modele\Entity;

class DevoirPromo {
    private int $idSalle;
    private int $idDevoir;
    private int $idPromo;
    private int $idMat;

    public function __construct($idSalle, $idDevoir, $idPromo, $idMat) {
        $this->idSalle = $idSalle;
        $this->idDevoir = $idDevoir;
        $this->idPromo = $idPromo;
        $this->idMat = $idMat;
    }

    public function getIdSalle():int{
        return $this->idSalle;
    }
    public function getIdDevoir():int{
        return $this->idDevoir;
    }
    public function getIdPromo():int{
        return $this->idPromo;
    }
    public function getIdMat():int{
        return $this->idMat;
    }
}
?>