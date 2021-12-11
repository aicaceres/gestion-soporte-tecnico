<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\Importados
 * @ORM\Table(name="importados")
 * @ORM\Entity()
 */
class Importados
{
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
     * @var string $nroserie
     * @ORM\Column(name="nroserie", type="string", nullable=true)
     */
    protected $nroserie;
    /**
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;
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
     * @ORM\Column(name="ubicacion_id", type="integer")
     */
    protected $ubicacionId;    
    /**
     * @ORM\Column(name="departamento", type="string", nullable=true)
     */
    protected $departamento;
    /**
     * @ORM\Column(name="localidad", type="string", nullable=true)
     */
    protected $localidad;
    /**
     * @ORM\Column(name="edificio", type="string", nullable=true)
     */
    protected $edificio;
    /**
     * @ORM\Column(name="edificio_id", type="integer")
     */
    protected $edificioId;    
    /**
     * @ORM\Column(name="piso", type="string", nullable=true)
     */
    protected $piso;
    /**
     * @ORM\Column(name="piso_id", type="integer")
     */
    protected $pisoId;    
    
    /**
     * @ORM\Column(name="estado", type="string", nullable=true)
     */
    protected $estado;
    /**
     * @ORM\Column(name="estado_id", type="integer")
     */
    protected $estadoId;        
    /**
     * @ORM\Column(name="concepto_entrega", type="string", nullable=true)
     */
    protected $conceptoEntrega;
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

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
     * @return Importados
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
     * Set nroserie
     *
     * @param string $nroserie
     * @return Importados
     */
    public function setNroserie($nroserie)
    {
        $this->nroserie = $nroserie;

        return $this;
    }

    /**
     * Get nroserie
     *
     * @return string 
     */
    public function getNroserie()
    {
        return $this->nroserie;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Importados
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set marca
     *
     * @param string $marca
     * @return Importados
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
     * @return Importados
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
     * @return Importados
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
     * Set departamento
     *
     * @param string $departamento
     * @return Importados
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;

        return $this;
    }

    /**
     * Get departamento
     *
     * @return string 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return Importados
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;

        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set edificio
     *
     * @param string $edificio
     * @return Importados
     */
    public function setEdificio($edificio)
    {
        $this->edificio = $edificio;

        return $this;
    }

    /**
     * Get edificio
     *
     * @return string 
     */
    public function getEdificio()
    {
        return $this->edificio;
    }

    /**
     * Set piso
     *
     * @param string $piso
     * @return Importados
     */
    public function setPiso($piso)
    {
        $this->piso = $piso;

        return $this;
    }

    /**
     * Get piso
     *
     * @return string 
     */
    public function getPiso()
    {
        return $this->piso;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Importados
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set conceptoEntrega
     *
     * @param string $conceptoEntrega
     * @return Importados
     */
    public function setConceptoEntrega($conceptoEntrega)
    {
        $this->conceptoEntrega = $conceptoEntrega;

        return $this;
    }

    /**
     * Get conceptoEntrega
     *
     * @return string 
     */
    public function getConceptoEntrega()
    {
        return $this->conceptoEntrega;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Importados
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set ubicacionId
     *
     * @param integer $ubicacionId
     * @return Importados
     */
    public function setUbicacionId($ubicacionId)
    {
        $this->ubicacionId = $ubicacionId;

        return $this;
    }

    /**
     * Get ubicacionId
     *
     * @return integer 
     */
    public function getUbicacionId()
    {
        return $this->ubicacionId;
    }

    /**
     * Set edificioId
     *
     * @param integer $edificioId
     * @return Importados
     */
    public function setEdificioId($edificioId)
    {
        $this->edificioId = $edificioId;

        return $this;
    }

    /**
     * Get edificioId
     *
     * @return integer 
     */
    public function getEdificioId()
    {
        return $this->edificioId;
    }

    /**
     * Set pisoId
     *
     * @param integer $pisoId
     * @return Importados
     */
    public function setPisoId($pisoId)
    {
        $this->pisoId = $pisoId;

        return $this;
    }

    /**
     * Get pisoId
     *
     * @return integer 
     */
    public function getPisoId()
    {
        return $this->pisoId;
    }

    /**
     * Set estadoId
     *
     * @param integer $estadoId
     * @return Importados
     */
    public function setEstadoId($estadoId)
    {
        $this->estadoId = $estadoId;

        return $this;
    }

    /**
     * Get estadoId
     *
     * @return integer 
     */
    public function getEstadoId()
    {
        return $this->estadoId;
    }
}
