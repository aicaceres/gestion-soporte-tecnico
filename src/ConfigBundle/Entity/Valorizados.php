<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\Valorizados
 * @ORM\Table(name="valorizado_equipos")
 * @ORM\Entity()
 */
class Valorizados {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="tipo", type="string", nullable=true)
     */
    protected $tipo;

    /**
     * @ORM\Column(name="marca", type="string", nullable=true)
     */
    protected $marca;

    /**
     * @ORM\Column(name="modelo", type="string", nullable=true)
     */
    protected $modelo;

    /**
     * @ORM\Column(name="ubicacion", type="string", nullable=true)
     */
    protected $ubicacion;

    /**
     * @ORM\Column(name="cantidad", type="string", nullable=true)
     */
    protected $cantidad;

    /**
     * @ORM\Column(name="valorusd", type="string", nullable=true)
     */
    protected $valorusd;

    /**
     * @ORM\Column(name="cotizacion", type="string", nullable=true)
     */
    protected $cotizacion;

    /**
     * @ORM\Column(name="archivo", type="string", nullable=true)
     */
    protected $archivo;

    /**
     * @ORM\Column(name="ubic_id", type="integer", nullable=true)
     */
    protected $ubicId;

    /**
     * @ORM\Column(name="edif_id", type="integer", nullable=true)
     */
    protected $edifId;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return Valorizados
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set marca
     *
     * @param string $marca
     * @return Valorizados
     */
    public function setMarca($marca)
    {
        $this->marca = $marca;

        return $this;
    }

    /**
     * Get marca
     *
     * @return string 
     */
    public function getMarca()
    {
        return $this->marca;
    }

    /**
     * Set modelo
     *
     * @param string $modelo
     * @return Valorizados
     */
    public function setModelo($modelo)
    {
        $this->modelo = $modelo;

        return $this;
    }

    /**
     * Get modelo
     *
     * @return string 
     */
    public function getModelo()
    {
        return $this->modelo;
    }

    /**
     * Set ubicacion
     *
     * @param string $ubicacion
     * @return Valorizados
     */
    public function setUbicacion($ubicacion)
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * Get ubicacion
     *
     * @return string 
     */
    public function getUbicacion()
    {
        return $this->ubicacion;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return Valorizados
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set valorusd
     *
     * @param string $valorusd
     * @return Valorizados
     */
    public function setValorusd($valorusd)
    {
        $this->valorusd = $valorusd;

        return $this;
    }

    /**
     * Get valorusd
     *
     * @return string 
     */
    public function getValorusd()
    {
        return $this->valorusd;
    }

    /**
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return Valorizados
     */
    public function setCotizacion($cotizacion)
    {
        $this->cotizacion = $cotizacion;

        return $this;
    }

    /**
     * Get cotizacion
     *
     * @return string 
     */
    public function getCotizacion()
    {
        return $this->cotizacion;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     * @return Valorizados
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;

        return $this;
    }

    /**
     * Get archivo
     *
     * @return string 
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set ubicId
     *
     * @param integer $ubicId
     * @return Valorizados
     */
    public function setUbicId($ubicId)
    {
        $this->ubicId = $ubicId;

        return $this;
    }

    /**
     * Get ubicId
     *
     * @return integer 
     */
    public function getUbicId()
    {
        return $this->ubicId;
    }

    /**
     * Set edifId
     *
     * @param integer $edifId
     * @return Valorizados
     */
    public function setEdifId($edifId)
    {
        $this->edifId = $edifId;

        return $this;
    }

    /**
     * Get edifId
     *
     * @return integer 
     */
    public function getEdifId()
    {
        return $this->edifId;
    }
}
