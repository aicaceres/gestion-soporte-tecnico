<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\OrdenTrabajoDetalle
 * @ORM\Table(name="orden_trabajo_detalle")
 * @ORM\Entity()
 * @Gedmo\Loggable()  
 */
class OrdenTrabajoDetalle
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Equipo", inversedBy="ordenesdetrabajo")
     *@ORM\JoinColumn(name="equipo_id", referencedColumnName="id") 
     * @Gedmo\Versioned()
     */
    protected $equipo;    
    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $descripcion;  

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\OrdenTrabajo", inversedBy="detalles")
     *@ORM\JoinColumn(name="orden_trabajo_id", referencedColumnName="id") 
     */
    protected $ordenTrabajo;  
    
    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\RequerimientoDetalle", inversedBy="ordenTrabajoDetalle",cascade={"persist"})
    * @ORM\JoinColumn(name="requerimiento_detalle_id", referencedColumnName="id", onDelete="CASCADE")
    */        
    protected $requerimientoDetalle;    
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Estado")
     * @ORM\JoinColumn(name="estado_original_id", referencedColumnName="id")
     */
    protected $estadoOriginal;     
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EquipoUbicacion")
     * @ORM\JoinColumn(name="equipo_ubicacion_original_id", referencedColumnName="id")     
     */
    protected $equipoUbicacionOriginal;    
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EquipoUbicacion")
     * @ORM\JoinColumn(name="equipo_ubicacion_final_id", referencedColumnName="id")
     */
    protected $equipoUbicacionFinal;    
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\EquipoUbicacion", mappedBy="ordenTrabajoDetalle")
     */    
    private $equipoUbicacionOrdenTrabajo;        
    /**
     * @ORM\Column(name="entregado", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $entregado = false;    
    
    /**
     * @ORM\Column(name="tipo_recambio", type="string", nullable=true)
     * En un recambio de equipo indica si se retira 'OUT' o si queda operativo 'IN'
     */
    protected $tipoRecambio = 'OUT';    
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tarea", mappedBy="ordenTrabajoDetalles")
     */
    protected $tareas;             
    
    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
    
    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;             


    public function __toString() {
        return strval($this->getId());
    }  
    public function getEquipoTextoOT() {
        return $this->getEquipo()->getTextoOT();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tareas = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set descripcion
     *
     * @param string $descripcion
     * @return OrdenTrabajoDetalle
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
     * Set entregado
     *
     * @param boolean $entregado
     * @return OrdenTrabajoDetalle
     */
    public function setEntregado($entregado)
    {
        $this->entregado = $entregado;

        return $this;
    }

    /**
     * Get entregado
     *
     * @return boolean 
     */
    public function getEntregado()
    {
        return $this->entregado;
    }

    /**
     * Set tipoRecambio
     *
     * @param string $tipoRecambio
     * @return OrdenTrabajoDetalle
     */
    public function setTipoRecambio($tipoRecambio)
    {
        $this->tipoRecambio = $tipoRecambio;

        return $this;
    }

    /**
     * Get tipoRecambio
     *
     * @return string 
     */
    public function getTipoRecambio()
    {
        return $this->tipoRecambio;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return OrdenTrabajoDetalle
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return OrdenTrabajoDetalle
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set equipo
     *
     * @param \AppBundle\Entity\Equipo $equipo
     * @return OrdenTrabajoDetalle
     */
    public function setEquipo(\AppBundle\Entity\Equipo $equipo = null)
    {
        $this->equipo = $equipo;

        return $this;
    }

    /**
     * Get equipo
     *
     * @return \AppBundle\Entity\Equipo 
     */
    public function getEquipo()
    {
        return $this->equipo;
    }

    /**
     * Set ordenTrabajo
     *
     * @param \AppBundle\Entity\OrdenTrabajo $ordenTrabajo
     * @return OrdenTrabajoDetalle
     */
    public function setOrdenTrabajo(\AppBundle\Entity\OrdenTrabajo $ordenTrabajo = null)
    {
        $this->ordenTrabajo = $ordenTrabajo;

        return $this;
    }

    /**
     * Get ordenTrabajo
     *
     * @return \AppBundle\Entity\OrdenTrabajo 
     */
    public function getOrdenTrabajo()
    {
        return $this->ordenTrabajo;
    }

    /**
     * Set requerimientoDetalle
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $requerimientoDetalle
     * @return OrdenTrabajoDetalle
     */
    public function setRequerimientoDetalle(\AppBundle\Entity\RequerimientoDetalle $requerimientoDetalle = null)
    {
        $this->requerimientoDetalle = $requerimientoDetalle;

        return $this;
    }

    /**
     * Get requerimientoDetalle
     *
     * @return \AppBundle\Entity\RequerimientoDetalle 
     */
    public function getRequerimientoDetalle()
    {
        return $this->requerimientoDetalle;
    }

    /**
     * Set estadoOriginal
     *
     * @param \ConfigBundle\Entity\Estado $estadoOriginal
     * @return OrdenTrabajoDetalle
     */
    public function setEstadoOriginal(\ConfigBundle\Entity\Estado $estadoOriginal = null)
    {
        $this->estadoOriginal = $estadoOriginal;

        return $this;
    }

    /**
     * Get estadoOriginal
     *
     * @return \ConfigBundle\Entity\Estado 
     */
    public function getEstadoOriginal()
    {
        return $this->estadoOriginal;
    }

    /**
     * Set equipoUbicacionOriginal
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipoUbicacionOriginal
     * @return OrdenTrabajoDetalle
     */
    public function setEquipoUbicacionOriginal(\AppBundle\Entity\EquipoUbicacion $equipoUbicacionOriginal = null)
    {
        $this->equipoUbicacionOriginal = $equipoUbicacionOriginal;

        return $this;
    }

    /**
     * Get equipoUbicacionOriginal
     *
     * @return \AppBundle\Entity\EquipoUbicacion 
     */
    public function getEquipoUbicacionOriginal()
    {
        return $this->equipoUbicacionOriginal;
    }

    /**
     * Set equipoUbicacionFinal
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipoUbicacionFinal
     * @return OrdenTrabajoDetalle
     */
    public function setEquipoUbicacionFinal(\AppBundle\Entity\EquipoUbicacion $equipoUbicacionFinal = null)
    {
        $this->equipoUbicacionFinal = $equipoUbicacionFinal;

        return $this;
    }

    /**
     * Get equipoUbicacionFinal
     *
     * @return \AppBundle\Entity\EquipoUbicacion 
     */
    public function getEquipoUbicacionFinal()
    {
        return $this->equipoUbicacionFinal;
    }

    /**
     * Set equipoUbicacionOrdenTrabajo
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipoUbicacionOrdenTrabajo
     * @return OrdenTrabajoDetalle
     */
    public function setEquipoUbicacionOrdenTrabajo(\AppBundle\Entity\EquipoUbicacion $equipoUbicacionOrdenTrabajo = null)
    {
        $this->equipoUbicacionOrdenTrabajo = $equipoUbicacionOrdenTrabajo;

        return $this;
    }

    /**
     * Get equipoUbicacionOrdenTrabajo
     *
     * @return \AppBundle\Entity\EquipoUbicacion 
     */
    public function getEquipoUbicacionOrdenTrabajo()
    {
        return $this->equipoUbicacionOrdenTrabajo;
    }

    /**
     * Add tareas
     *
     * @param \AppBundle\Entity\Tarea $tareas
     * @return OrdenTrabajoDetalle
     */
    public function addTarea(\AppBundle\Entity\Tarea $tareas)
    {
        $this->tareas[] = $tareas;

        return $this;
    }

    /**
     * Remove tareas
     *
     * @param \AppBundle\Entity\Tarea $tareas
     */
    public function removeTarea(\AppBundle\Entity\Tarea $tareas)
    {
        $this->tareas->removeElement($tareas);
    }

    /**
     * Get tareas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTareas()
    {
        return $this->tareas;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return OrdenTrabajoDetalle
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return OrdenTrabajoDetalle
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
