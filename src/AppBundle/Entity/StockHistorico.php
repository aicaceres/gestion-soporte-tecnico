<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * AppBundle\Entity\StockHistorico
 *
 * @ORM\Table(name="stock_historico")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockHistorico extends Controller {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var date $fecha
     * @ORM\Column(name="fecha", type="date", nullable=false)
     */
    private $fecha;

    /**
     * @var string $tipo
     * @ORM\Column(name="tipo", type="string", nullable=false)
     */
    // Nombre de la tabla de donde se guarda el id de movimiento.
    private $tipo;

    /**
     * @var integer $movimiento
     * @ORM\Column(name="movimiento", type="integer")
     */
    private $movimiento;

    /**
     * @var string $signo
     * @ORM\Column(name="signo", type="string")
     */
    private $signo = '+';

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2 )
     */
    protected $cantidad;

    /**
     * @var integer $stock
     * @ORM\Column(name="stock", type="decimal", scale=2, nullable=true )
     */
    protected $stock;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo", inversedBy="stockHistorico")
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $insumo;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;
    public $nroComprobante;
    public $equipo;
    public $urlMovimiento;

    public function getTipoMovimiento() {
        switch ($this->getTipo()) {
            case 'AJUSTE':
                return 'Ajuste de Stock';
            case 'COMPRA':
                return 'Compra de Insumos';
            case 'MOVIMIENTO':
                return 'Movimiento Interdepósito';
            case 'SOPORTE':
                return 'Soporte Técnico';
            case 'ENTREGAINSUMO':
                return 'Entrega de Insumo';
            default:
                return NULL;
        }
    }

    public function getEntidadMovimiento() {
        switch ($this->getTipo()) {
            case 'AJUSTE':
                return 'AppBundle:StockAjuste';
            case 'COMPRA':
                return 'AppBundle:Compra';
            case 'MOVIMIENTO':
                return 'AppBundle:StockMovimiento';
            case 'SOPORTE':
                return 'AppBundle:InsumoxTarea';
            case 'ENTREGAINSUMO':
                return 'AppBundle:InsumoEntrega';
            default:
                return NULL;
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return StockHistorico
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return StockHistorico
     */
    public function setTipo($tipo) {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo() {
        return $this->tipo;
    }

    /**
     * Set movimiento
     *
     * @param integer $movimiento
     * @return StockHistorico
     */
    public function setMovimiento($movimiento) {
        $this->movimiento = $movimiento;

        return $this;
    }

    /**
     * Get movimiento
     *
     * @return integer
     */
    public function getMovimiento() {
        return $this->movimiento;
    }

    /**
     * Set signo
     *
     * @param string $signo
     * @return StockHistorico
     */
    public function setSigno($signo) {
        $this->signo = $signo;

        return $this;
    }

    /**
     * Get signo
     *
     * @return string
     */
    public function getSigno() {
        return $this->signo;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return StockHistorico
     */
    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string
     */
    public function getCantidad() {
        return $this->cantidad;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return StockHistorico
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set deposito
     *
     * @param \ConfigBundle\Entity\Departamento $deposito
     * @return StockHistorico
     */
    public function setDeposito(\ConfigBundle\Entity\Departamento $deposito = null) {
        $this->deposito = $deposito;

        return $this;
    }

    /**
     * Get deposito
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getDeposito() {
        return $this->deposito;
    }

    /**
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return StockHistorico
     */
    public function setInsumo(\AppBundle\Entity\Insumo $insumo = null) {
        $this->insumo = $insumo;

        return $this;
    }

    /**
     * Get insumo
     *
     * @return \AppBundle\Entity\Insumo
     */
    public function getInsumo() {
        return $this->insumo;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return StockHistorico
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set stock
     *
     * @param string $stock
     * @return StockHistorico
     */
    public function setStock($stock) {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return string
     */
    public function getStock() {
        return $this->stock;
    }

}