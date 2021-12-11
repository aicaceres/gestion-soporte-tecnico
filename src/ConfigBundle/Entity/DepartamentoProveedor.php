<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\DepartamentoProveedor
 * @ORM\Table(name="departamento_proveedor")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\UbicacionRepository")
 */
class DepartamentoProveedor
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string $enlaceProveedor
     * @ORM\Column(name="enlace_proveedor", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    protected $enlaceProveedor;
    
    /**
     * @var string $enlaceTipoConexion
     * @ORM\Column(name="enlace_tipo_conexion", type="string", nullable=true)
     */    
    protected $enlaceTipoConexion;
    
    /**
     * @var string $enlaceTelefonoReclamo
     * @ORM\Column(name="enlace_telefono_reclamo", type="string", nullable=true)
     */
    protected $enlaceTelefonoReclamo;    
    
    /**
     * @var string $enlaceEmailReclamo
     * @ORM\Column(name="enlace_email_reclamo", type="string", nullable=true)
     */
    protected $enlaceEmailReclamo;    
    
    /**
     * @var string $enlaceReferenciaCliente
     * @ORM\Column(name="enlace_referencia_cliente", type="string", nullable=true)
     */
    protected $enlaceReferenciaCliente;    
    
    /**
     * @var string $internetProveedor
     * @ORM\Column(name="internet_proveedor", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    protected $internetProveedor;
    
    /**
     * @var string $internetTipoConexion
     * @ORM\Column(name="internet_tipo_conexion", type="string", nullable=true)
     */    
    protected $internetTipoConexion;
    
    /**
     * @var string $internetTelefonoReclamo
     * @ORM\Column(name="internet_telefono_reclamo", type="string", nullable=true)
     */
    protected $internetTelefonoReclamo;    
    
    /**
     * @var string $internetEmailReclamo
     * @ORM\Column(name="internet_email_reclamo", type="string", nullable=true)
     */
    protected $internetEmailReclamo;    
    
    /**
     * @var string $internetReferenciaCliente
     * @ORM\Column(name="internet_referencia_cliente", type="string", nullable=true)
     */
    protected $internetReferenciaCliente;                        
    
    /**
     * @var string $observaciones
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
    * @ORM\OneToOne(targetEntity="ConfigBundle\Entity\Departamento", inversedBy="proveedor")
    * @ORM\JoinColumn(name="departamento_id", referencedColumnName="id", onDelete="CASCADE")
    */
    protected $departamento;     
    
     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reclamo", mappedBy="proveedor", orphanRemoval=true,cascade={"persist", "remove"})
     */
    protected $reclamos;    
    
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
    
    public function getReclamosAbiertos(){
        $cant = 0;        
        foreach ($this->getReclamos() as $reclamo){
            if($reclamo->getAbierto()==1){
                $cant = $cant + 1;
            }
        }
        return $cant;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reclamos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set enlaceProveedor
     *
     * @param string $enlaceProveedor
     * @return DepartamentoProveedor
     */
    public function setEnlaceProveedor($enlaceProveedor)
    {
        $this->enlaceProveedor = $enlaceProveedor;

        return $this;
    }

    /**
     * Get enlaceProveedor
     *
     * @return string 
     */
    public function getEnlaceProveedor()
    {
        return $this->enlaceProveedor;
    }

    /**
     * Set enlaceTipoConexion
     *
     * @param string $enlaceTipoConexion
     * @return DepartamentoProveedor
     */
    public function setEnlaceTipoConexion($enlaceTipoConexion)
    {
        $this->enlaceTipoConexion = $enlaceTipoConexion;

        return $this;
    }

    /**
     * Get enlaceTipoConexion
     *
     * @return string 
     */
    public function getEnlaceTipoConexion()
    {
        return $this->enlaceTipoConexion;
    }

    /**
     * Set enlaceTelefonoReclamo
     *
     * @param string $enlaceTelefonoReclamo
     * @return DepartamentoProveedor
     */
    public function setEnlaceTelefonoReclamo($enlaceTelefonoReclamo)
    {
        $this->enlaceTelefonoReclamo = $enlaceTelefonoReclamo;

        return $this;
    }

    /**
     * Get enlaceTelefonoReclamo
     *
     * @return string 
     */
    public function getEnlaceTelefonoReclamo()
    {
        return $this->enlaceTelefonoReclamo;
    }

    /**
     * Set enlaceEmailReclamo
     *
     * @param string $enlaceEmailReclamo
     * @return DepartamentoProveedor
     */
    public function setEnlaceEmailReclamo($enlaceEmailReclamo)
    {
        $this->enlaceEmailReclamo = $enlaceEmailReclamo;

        return $this;
    }

    /**
     * Get enlaceEmailReclamo
     *
     * @return string 
     */
    public function getEnlaceEmailReclamo()
    {
        return $this->enlaceEmailReclamo;
    }

    /**
     * Set enlaceReferenciaCliente
     *
     * @param string $enlaceReferenciaCliente
     * @return DepartamentoProveedor
     */
    public function setEnlaceReferenciaCliente($enlaceReferenciaCliente)
    {
        $this->enlaceReferenciaCliente = $enlaceReferenciaCliente;

        return $this;
    }

    /**
     * Get enlaceReferenciaCliente
     *
     * @return string 
     */
    public function getEnlaceReferenciaCliente()
    {
        return $this->enlaceReferenciaCliente;
    }

    /**
     * Set internetProveedor
     *
     * @param string $internetProveedor
     * @return DepartamentoProveedor
     */
    public function setInternetProveedor($internetProveedor)
    {
        $this->internetProveedor = $internetProveedor;

        return $this;
    }

    /**
     * Get internetProveedor
     *
     * @return string 
     */
    public function getInternetProveedor()
    {
        return $this->internetProveedor;
    }

    /**
     * Set internetTipoConexion
     *
     * @param string $internetTipoConexion
     * @return DepartamentoProveedor
     */
    public function setInternetTipoConexion($internetTipoConexion)
    {
        $this->internetTipoConexion = $internetTipoConexion;

        return $this;
    }

    /**
     * Get internetTipoConexion
     *
     * @return string 
     */
    public function getInternetTipoConexion()
    {
        return $this->internetTipoConexion;
    }

    /**
     * Set internetTelefonoReclamo
     *
     * @param string $internetTelefonoReclamo
     * @return DepartamentoProveedor
     */
    public function setInternetTelefonoReclamo($internetTelefonoReclamo)
    {
        $this->internetTelefonoReclamo = $internetTelefonoReclamo;

        return $this;
    }

    /**
     * Get internetTelefonoReclamo
     *
     * @return string 
     */
    public function getInternetTelefonoReclamo()
    {
        return $this->internetTelefonoReclamo;
    }

    /**
     * Set internetEmailReclamo
     *
     * @param string $internetEmailReclamo
     * @return DepartamentoProveedor
     */
    public function setInternetEmailReclamo($internetEmailReclamo)
    {
        $this->internetEmailReclamo = $internetEmailReclamo;

        return $this;
    }

    /**
     * Get internetEmailReclamo
     *
     * @return string 
     */
    public function getInternetEmailReclamo()
    {
        return $this->internetEmailReclamo;
    }

    /**
     * Set internetReferenciaCliente
     *
     * @param string $internetReferenciaCliente
     * @return DepartamentoProveedor
     */
    public function setInternetReferenciaCliente($internetReferenciaCliente)
    {
        $this->internetReferenciaCliente = $internetReferenciaCliente;

        return $this;
    }

    /**
     * Get internetReferenciaCliente
     *
     * @return string 
     */
    public function getInternetReferenciaCliente()
    {
        return $this->internetReferenciaCliente;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return DepartamentoProveedor
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
     * Set departamento
     *
     * @param \ConfigBundle\Entity\Departamento $departamento
     * @return DepartamentoProveedor
     */
    public function setDepartamento(\ConfigBundle\Entity\Departamento $departamento = null)
    {
        $this->departamento = $departamento;

        return $this;
    }

    /**
     * Get departamento
     *
     * @return \ConfigBundle\Entity\Departamento 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Add reclamos
     *
     * @param \AppBundle\Entity\Reclamo $reclamos
     * @return DepartamentoProveedor
     */
    public function addReclamo(\AppBundle\Entity\Reclamo $reclamos)
    {
        $this->reclamos[] = $reclamos;

        return $this;
    }

    /**
     * Remove reclamos
     *
     * @param \AppBundle\Entity\Reclamo $reclamos
     */
    public function removeReclamo(\AppBundle\Entity\Reclamo $reclamos)
    {
        $this->reclamos->removeElement($reclamos);
    }

    /**
     * Get reclamos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReclamos()
    {
        return $this->reclamos;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return DepartamentoProveedor
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
     * @return DepartamentoProveedor
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return DepartamentoProveedor
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
     * @return DepartamentoProveedor
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
