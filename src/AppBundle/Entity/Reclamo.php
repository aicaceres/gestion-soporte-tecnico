<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Reclamo
 * @ORM\Table(name="reclamo")
* @ORM\Entity(repositoryClass="AppBundle\Entity\ReclamoRepository")
 */
class Reclamo
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;     
    /**
     * @var string $tipoProveedor
     * @ORM\Column(name="tipo_proveedor", type="string", length=1, nullable=true)
     * E = enlace o vinculo * I = internet
     */
    protected $tipoProveedor;     
    /**
     * @var string $nroReferencia
     * @ORM\Column(name="nro_referencia", type="string", nullable=true)
     */
    protected $nroReferencia;     
    /**
     * @var string $referente
     * @ORM\Column(name="referente", type="string", nullable=true)
     */
    protected $referente;     
    /**
     * @var string $detalle
     * @ORM\Column(name="detalle", type="text", nullable=true)
     */    
    protected $detalle; 
    /**
     * @var string $resumen
     * @ORM\Column(name="resumen", type="text", nullable=true)
     */    
    protected $resumen; 
  
     /**
     *@ORM\ManyToOne(targetEntity="ConfigBundle\Entity\DepartamentoProveedor", inversedBy="reclamos",cascade={"persist"})
     *@ORM\JoinColumn(name="departamento_proveedor_id", referencedColumnName="id") 
     */
    protected $proveedor;              
    
    /**
     * @ORM\Column(name="abierto", type="boolean", nullable=true)
     */
    protected $abierto = true;
    
    /**
     * fecha de creacion
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;
    /**
     * Usuario emisor
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;    

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;     

    public function getIconoProveedor(){
        $texto = '';
        if( $this->getTipoProveedor()){
            $texto = ( $this->getTipoProveedor()=='E' ) ? 
                '<i class="glyphicon glyphicon-link"></i>' : 
                '<i class="fa fa-internet-explorer"></i>';
        }
        return $texto;
    }
    
    public function getNombreProveedor(){
        $texto = '';
        if( $this->getTipoProveedor()){
            $texto = ( $this->getTipoProveedor()=='E' ) ? 
                $this->getProveedor()->getEnlaceProveedor() : 
                $this->getProveedor()->getInternetProveedor();
        }
        return $texto;
    }
    
    public function getTipoConexionProveedor(){
        $texto = '';
        if( $this->getTipoProveedor()){
            $texto = ( $this->getTipoProveedor()=='E' ) ? 
                $this->getProveedor()->getEnlaceTipoConexion() : 
                $this->getProveedor()->getInternetTipoConexion();
        }
        return $texto;
    }
    
    public function getTelefonoProveedor(){
        $texto = '';
        if( $this->getTipoProveedor()){
            $texto = ( $this->getTipoProveedor()=='E' ) ? 
                $this->getProveedor()->getEnlaceTelefonoReclamo() : 
                $this->getProveedor()->getInternetTelefonoReclamo();
        }
        return $texto;
    }
    
    public function getEmailProveedor(){
        $texto = '';
        if( $this->getTipoProveedor()){
            $texto = ( $this->getTipoProveedor()=='E' ) ? 
                $this->getProveedor()->getEnlaceEmailReclamo() : 
                $this->getProveedor()->getInternetEmailReclamo();
        }
        return $texto;
    }

    public function getReferenciaProveedor(){
        $texto = '';
        if( $this->getTipoProveedor()){
            $texto = ( $this->getTipoProveedor()=='E' ) ? 
                $this->getProveedor()->getEnlaceReferenciaCliente() : 
                $this->getProveedor()->getInternetReferenciaCliente();
        }
        return $texto;
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Reclamo
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set nroReferencia
     *
     * @param string $nroReferencia
     * @return Reclamo
     */
    public function setNroReferencia($nroReferencia)
    {
        $this->nroReferencia = $nroReferencia;

        return $this;
    }

    /**
     * Get nroReferencia
     *
     * @return string 
     */
    public function getNroReferencia()
    {
        return $this->nroReferencia;
    }

    /**
     * Set referente
     *
     * @param string $referente
     * @return Reclamo
     */
    public function setReferente($referente)
    {
        $this->referente = $referente;

        return $this;
    }

    /**
     * Get referente
     *
     * @return string 
     */
    public function getReferente()
    {
        return $this->referente;
    }

    /**
     * Set detalle
     *
     * @param string $detalle
     * @return Reclamo
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;

        return $this;
    }

    /**
     * Get detalle
     *
     * @return string 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Reclamo
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
     * @return Reclamo
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
     * Set proveedor
     *
     * @param \ConfigBundle\Entity\DepartamentoProveedor $proveedor
     * @return Reclamo
     */
    public function setProveedor(\ConfigBundle\Entity\DepartamentoProveedor $proveedor = null)
    {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ConfigBundle\Entity\DepartamentoProveedor 
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Reclamo
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
     * @return Reclamo
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

    /**
     * Set abierto
     *
     * @param boolean $abierto
     * @return Reclamo
     */
    public function setAbierto($abierto)
    {
        $this->abierto = $abierto;

        return $this;
    }

    /**
     * Get abierto
     *
     * @return boolean 
     */
    public function getAbierto()
    {
        return $this->abierto;
    }

    /**
     * Set resumen
     *
     * @param string $resumen
     * @return Reclamo
     */
    public function setResumen($resumen)
    {
        $this->resumen = $resumen;

        return $this;
    }

    /**
     * Get resumen
     *
     * @return string 
     */
    public function getResumen()
    {
        return $this->resumen;
    }

    /**
     * Set tipoProveedor
     *
     * @param string $tipoProveedor
     * @return Reclamo
     */
    public function setTipoProveedor($tipoProveedor)
    {
        $this->tipoProveedor = $tipoProveedor;

        return $this;
    }

    /**
     * Get tipoProveedor
     *
     * @return string 
     */
    public function getTipoProveedor()
    {
        return $this->tipoProveedor;
    }
}
