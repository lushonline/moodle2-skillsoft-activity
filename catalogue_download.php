<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   mod-skillsoft
 * @author    Phil Lello <philipl@catalyst-eu.net>
 * @copyright 2014 Catalyst IT Europe Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/Writer/Excel2007.php');

require_login();
$context = context_system::instance();
require_capability('moodle/course:create', $context);

$spreadsheet = new PHPExcel();
$spreadsheet->setActiveSheetIndex(0);
$skillsoft_sheet = $spreadsheet->getActiveSheet();
$skillsoft_sheet->setTitle('Skillsoft');
$category_sheet = $spreadsheet->createSheet();
$category_sheet->setTitle('Categories');

// Setup the category sheet
$i = 0;
$categories = coursecat::make_categories_list();
foreach ($categories as $category) {
    $cell = 'A'.($i+1);
    $category_sheet->setCellValue($cell, $category);
    $i++;
}
$spreadsheet->addNamedRange(new PHPExcel_NamedRange('Categories', $category_sheet, 'A1:A'.count($categories)));

// Load the assets
$assetdoc = skillsoft_asset_metadata_document();
$xpath = new DOMXPath($assetdoc);
$xpath->registerNamespace('olsa', 'http://www.skillsoft.com/services/olsa_v1_0/');
$xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
$assets = $xpath->query('/olsa:metadata/olsa:asset');
$columns = array(
    'asset'       => 'dc:identifier',
    'language'    => 'dc:language',
    'title'       => 'dc:title',
    'description' => 'dc:description',
    'duration'    => 'olsa:duration',
);
for ($i = 0; $i < $assets->length; $i++) {
    set_time_limit(30);
    $asset = $assets->item($i);
    $properties = array();
    for ($j = 0; $j < $asset->childNodes->length; $j++) {
        $child = $asset->childNodes->item($j);
        $properties[$child->nodeName] = $child->textContent;
    }
    $j = 0;
    foreach ($columns as $name => $key) {
        if ($i == 0) {
            $cell = PHPExcel_Cell::stringFromColumnIndex($j).'1';
            $skillsoft_sheet->setCellValue($cell, $name);
        }
        $cell = PHPExcel_Cell::stringFromColumnIndex($j).($i+2);
        if ($name == 'description') {
            $skillsoft_sheet->getStyle($cell)->getAlignment()->setWrapText(true);
        }
        $skillsoft_sheet->getRowDimension($i+2)->setRowHeight(-1);
        $skillsoft_sheet->setCellValue($cell, $properties[$key]);
        $j++;
    }
    // Placeholder for category
    if ($i == 0) {
        $cell = PHPExcel_Cell::stringFromColumnIndex($j).'1';
        $skillsoft_sheet->setCellValue($cell, 'category');
        $validation = $skillsoft_sheet->getCell($cell)->getDataValidation();
        $validation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
        $validation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('=Categories');
    }
    $cell = PHPExcel_Cell::stringFromColumnIndex($j).($i+2);
    $skillsoft_sheet->getCell($cell)->setDataValidation(clone $validation);
    $j++;

    // Placeholder for topics
    if ($i == 0) {
        $cell = PHPExcel_Cell::stringFromColumnIndex($j).'1';
        $skillsoft_sheet->setCellValue($cell, 'topics');
    }
    $j++;

    // Autofilter
    if ($i == 0) {
        $col = PHPExcel_Cell::stringFromColumnIndex($j-1);
        $skillsoft_sheet->setAutoFilter('A1:'.$col.'1');
    }
}
for ($i = 1; $i < $j; $i++) {
    $col = PHPExcel_Cell::stringFromColumnIndex($i);
    $skillsoft_sheet->getColumnDimension($col)->setAutoSize(true);
}
$skillsoft_sheet->calculateColumnWidths();

header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="skillsoft.xls"');
$objWriter = new PHPExcel_Writer_Excel2007($spreadsheet);
$objWriter->save('php://output');
