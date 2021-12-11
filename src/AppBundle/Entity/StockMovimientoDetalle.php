<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * AppBundle\Entity\StockMovimientoDetalle
 *
 * @ORM\Table(name="stock_movimiento_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockMovimientoDetalle
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
     /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2)
     */
    protected $cantidad;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo")
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $insumo; 

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\StockMovimiento", inversedBy="detalles")
     *@ORM\JoinColumn(name="stock_movimiento_id", referencedColumnName="id") 
     */
    protected $stockMovimiento;    


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
     * Set cantidad
     *
     * @param string $cantidad
     * @return StockMovimientoDetalle
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
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return StockMovimientoDetalle
     */
    public function setInsumo(\AppBundle\Entity\Insumo $insumo = null)
    {
        $this->insumo = $insumo;

        return $this;
    }

    /**
     * Get insumo
     *
     * @return \AppBundle\Entity\Insumo 
     */
    public function getInsumo()
    {
        return $this->insumo;
    }

    /**
     * Set stockMovimiento
     *
     * @param \AppBundle\Entity\StockMovimiento $stockMovimiento
     * @return StockMovimientoDetalle
     */
    public function setStockMovimiento(\AppBundle\Entity\StockMovimiento $stockMovimiento = null)
    {
        $this->stockMovimiento = $stockMovimiento;

        return $this;
    }

    /**
     * Get stockMovimiento
     *
     * @return \AppBundle\Entity\StockMovimiento 
     */
    public function getStockMovimiento()
    {
        return $this->stockMovimiento;
    }
}
