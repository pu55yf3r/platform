<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter as SonataDateRangeFilter;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateRangeFilter extends SonataDateRangeFilter implements FilterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_date_range';
        return $renderSettings;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            DateRangeType::TYPE_BETWEEN
                => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            DateRangeType::TYPE_NOT_BETWEEN
                => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );
    }
}
