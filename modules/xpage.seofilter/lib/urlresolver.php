<?php namespace Xpage\SeoFilter;

class UrlResolver implements UrlResolverInterface
{
    protected $properties = [];
    protected $values = [];
    protected $urlParts = [];
    protected $variables = [];

    /**
     * @var ValueModifierInterface
     */
    protected $valueModifier;

    public function __construct(ValueModifierInterface $valueModifier) {
        $this->valueModifier = $valueModifier;
        $this->setSeoFilterProperties();
        $this->setPropertyValues();
    }

    /**
     * @return array
     */
    public function getVariables() {
        return $this->variables;
    }

    public function resolveUrl($cleanUrl) {
        $this->urlParts = explode("/", $cleanUrl);
        if($this->checkForSeoFilter()) {
            return true;
        }

        return false;
    }

    protected function checkForSeoFilter() {
        if($section = $this->findSection()) {
            $this->variables['SECTION_ID'] = $section['ID'];
            if($propertyValues = $this->findPropertyValues()) {
                $this->variables['PROPERTY_VALUES'] = $propertyValues;
                $this->variables['SEO_URL'] = implode("/", $this->urlParts);
                return true;
            }
        }

        return false;
    }

    protected function setSeoFilterProperties() {
        $this->properties = PropertyTable::getList([
            'select' =>
                [
                    'PROPERTY_ID',
                    'SORT',
                ],
            'order'  =>
                [
                    'SORT' => 'ASC'
                ]
        ])->fetchAll();
    }

    protected function setPropertyValues() {
        $values = [];
        $arValues = \Bitrix\Iblock\PropertyEnumerationTable::getList([
            'filter' =>
                [
                    'PROPERTY_ID' => \Xpage\Helper::array_pluck($this->properties, 'PROPERTY_ID')
                ],
            'select' =>
                [
                    'ID',
                    'VALUE',
                    'PROPERTY_ID'
                ],
            'order'  =>
                [
                    'SEOFILTER_PROPERTY_TABLE.SORT' => 'ASC'
                ],
            'runtime' =>
                [
                    new \Bitrix\Main\Entity\ReferenceField(
                        'SEOFILTER_PROPERTY_TABLE',
                        '\\Xpage\\Seofilter\\Property',
                        ['=this.PROPERTY_ID' => 'ref.PROPERTY_ID']
                    ),

                ]
        ])->fetchAll();

        foreach($arValues as $value) {
            $values[$value['PROPERTY_ID']][] = [
                'VALUE_ID' => $value['ID'],
                'VALUE'    => $this->valueModifier->modify($value['VALUE'])
            ];
        }

        $this->values = $values;
    }

    protected function findSection() {
        $lastSection = false;
        foreach($this->urlParts as $id => $part) {
            $section = \Bitrix\Iblock\SectionTable::getList([
                'filter' =>
                    [
                        'IBLOCK_ID'         => \Bitrix\Main\Config\Option::get('xpage.seofilter', 'IBLOCK', 1),
                        'IBLOCK_SECTION_ID' => isset($section) ? $section['ID'] : NULL,
                        'CODE'              => $part
                    ]
            ])->fetch();

            if($section) {
                $lastSection = $section;
                unset($this->urlParts[$id]);
            }
        }

        return $lastSection;
    }

    protected function findPropertyValues() {
        $propertyValues = [];
        $order = [];
        foreach($this->urlParts as $id => $part) {
            $found = false;
            foreach($this->values as $prop => $values) {
                $stringValues = \Xpage\Helper::array_pluck($values, 'VALUE');
                $key = array_search($part, $stringValues);
                if($key !== false && !in_array($prop, $order)) {
                    $order[] = $prop;
                    $propertyValues[$prop] = $values[$key]['VALUE_ID'];
                    $found = true;
                    break;
                }
            }
            if(!$found) return false;
        }
        if(!$this->isOrderRight($order)) return false;

        return $propertyValues;
    }

    protected function isOrderRight($order) {
        $rightOrder = \Xpage\Helper::array_pluck($this->properties, 'PROPERTY_ID');
        $lastKey = 0;
        foreach($order as $id => $prop) {
            $key = array_search($prop, $rightOrder);
            if($key === false || $key < $lastKey) return false;
            $lastKey = $key;
        }

        return true;
    }
}