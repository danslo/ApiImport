<?php
/*
 * Copyright 2011 Daniel Sloof
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

class Danslo_ApiImport_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    /**
     * Zend Date To local date according Map array
     *
     * @var array
     */
    private static $_convertZendToStrftimeDate = array(
        'yyyy-MM-ddTHH:mm:ssZZZZ' => '%c',
        'EEEE' => '%A',
        'EEE'  => '%a',
        'D'    => '%j',
        'MMMM' => '%B',
        'MMM'  => '%b',
        'MM'   => '%m',
        'M'    => '%m',
        'dd'   => '%d',
        'd'    => '%e',
        'yyyy' => '%Y',
        'yy'   => '%Y',
        'y'    => '%Y'
    );
    /**
     * Zend Date To local time according Map array
     *
     * @var array
     */
    private static $_convertZendToStrftimeTime = array(
        'a'  => '%p',
        'hh' => '%I',
        'h'  => '%I',
        'HH' => '%H',
        'H'  => '%H',
        'mm' => '%M',
        'ss' => '%S',
        'z'  => '%Z',
        'v'  => '%Z'
    );

    /**
     * Convert Zend Date format to local time/date according format
     *
     * @param string $value
     * @param boolean $convertDate
     * @param boolean $convertTime
     * @return string
     */
    public static function convertZendToStrftime($value, $convertDate = true, $convertTime = true)
    {
        if ($convertTime) {
            $value = self::_convert($value, self::$_convertZendToStrftimeTime);
        }
        if ($convertDate) {
            $value = self::_convert($value, self::$_convertZendToStrftimeDate);
        }
        return $value;
    }

    /**
     * Convert value by dictionary
     *
     * @param string $value
     * @param array $dictionary
     * @return string
     */
    protected static function _convert($value, $dictionary)
    {
        foreach ($dictionary as $search => $replace) {
            $value = preg_replace('/(^|[^%])' . $search . '/', '$1' . $replace, $value);
        }
        return $value;
    }

}
