<?php 
namespace App\Modele\Entity;

class Enseignement {
    private int $_idEns;
    private int $_idMat;

    public function __construct(int $idEns, int $idMat) {
        $this->setIdEns($idEns);
        $this->setIdMat($idMat);
    }

    public function getIdEns(): int {
        return $this->_idEns;
    }

    public function setIdEns(int $val) {
        $this->_idEns = $val;
    }

    public function getIdMat(): int {
        return $this->_idMat;
    }

    public function setIdMat(int $val) {
        $this->_idMat = $val;
    }
}