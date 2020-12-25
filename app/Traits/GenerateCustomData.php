<?php

namespace App\Traits;

use App\Models\{Doctor, MasterFile, Template, CustomizationInput};
use Log;

trait GenerateCustomData
{
    /**
     * @param Doctor $doctor
     * @param MasterFile $masterfile
     * @return array $customBox
     */
    function generateMasterFileInputData(Doctor $doctor, MasterFile $masterfile)
    {
        Log::info('MasterFile Data', [
            'masterfile_data' => $masterfile->customInputsMapping()
        ]);

        if(! $masterfile->customInputsMapping()->exists()) {
            return [];
        }

        $customBox = [];
        $getInputMapping = $masterfile->customInputsMapping()->get(['input_id', 'type', 'form_id', 'field', 'static_text', 'static_file'])->toArray();
        
        Log::info('Custom Mapping Data', [
            'mapping_data' => $getInputMapping
        ]);

        foreach ($getInputMapping as $formInputs) {
            $field = $formInputs["field"];
            $inputId = $formInputs["input_id"];
            $type = $formInputs["type"];
            $field = $formInputs["field"];
            $staticText = $formInputs["static_text"];
            $staticFile = $formInputs["static_file"];

            $inputData = CustomizationInput::where(['id' => $inputId])->first();

            if(!$doctor || !$inputData) { continue; }

            $customizedInputType = $inputData->type;
            $doctorInfo = isset($doctor->info) ? $doctor->info : NULL;
            $inputSettings = isset($inputData->settings) ? $inputData->settings : NULL;
            
            $customBoxInner = [];
            
            if ($type === 'static') {
                switch ($customizedInputType) {
                    case 'file':
                        $customBoxInner['type'] = 'image';
                        $customBoxInner['url'] = $staticFile;
                        $customBoxInner['x'] = $inputSettings['dimension']['x'] ?? NULL;
                        $customBoxInner['y'] = $inputSettings['dimension']['y'] ?? NULL;
                        $customBoxInner['width'] = $inputSettings['dimension']['width'] ?? NULL;
                        $customBoxInner['height'] = $inputSettings['dimension']['height'] ?? NULL;
                        $customBoxInner['border'] = 'none';
                    
                        break;
    
                    case 'text':
                        $customBoxInner['type'] = 'text';
                        $customBoxInner['text'] = $staticText;
                        $customBoxInner['x'] = $inputSettings['alignment_x'] ?? NULL;
                        $customBoxInner['y'] = $inputSettings['alignment_y'] ?? NULL;
                        $customBoxInner['font_color'] = 'Black';
                        $customBoxInner['font_size'] = $inputSettings['font_size'] ?? NULL;
                        $customBoxInner['font'] = 'https://ff.static.1001fonts.net/s/c/scriptina.regular.ttf';

                        break;
                    
                    default:
                        break;
                }
            } else if($type === 'form') {
                switch ($customizedInputType) {
                    case 'file':
                        $customBoxInner['type'] = 'image';
                        $customBoxInner['url'] = $doctorInfo[$field] ?? NULL;
                        $customBoxInner['x'] = $inputSettings['dimension']['x'] ?? NULL;
                        $customBoxInner['y'] = $inputSettings['dimension']['y'] ?? NULL;
                        $customBoxInner['width'] = $inputSettings['dimension']['width'] ?? NULL;
                        $customBoxInner['height'] = $inputSettings['dimension']['height'] ?? NULL;
                        $customBoxInner['border'] = 'none';
                        break;
    
                    case 'text':
                        $customBoxInner['type'] = 'text';
                        $customBoxInner['text'] = $doctorInfo[$field] ?? NULL;
                        $customBoxInner['x'] = $inputSettings['alignment_x'] ?? NULL;
                        $customBoxInner['y'] = $inputSettings['alignment_y'] ?? NULL;
                        $customBoxInner['font_color'] = 'Black';
                        $customBoxInner['font_size'] = $inputSettings['font_size'] ?? NULL;
                        $customBoxInner['font_family'] = 'https://ff.static.1001fonts.net/s/c/scriptina.regular.ttf';
                        break;
                    
                    default:
                        break;
                }
            }

            array_push($customBox, $customBoxInner);
        }

        return $customBox;        
    }

    /**
     * @param Doctor $doctor
     * @param Template $template
     * @return array $customBox
     */
    function generateTemplateInputData(Doctor $doctor, Template $template = NULL)
    {
        Log::info('Template Data', [
            'template_data' => $template->customInputsMapping()
        ]);

        if(! $template->customInputsMapping()->exists()) {
            return [];
        }

        $customBox = [];
        $getInputMapping = $template->customInputsMapping()->get(['input_id', 'type', 'form_id', 'field', 'static_text', 'static_file'])->toArray();
        
        foreach ($getInputMapping as $formInputs) {
            $field = $formInputs["field"];
            $inputId = $formInputs["input_id"];
            $type = $formInputs["type"];
            $field = $formInputs["field"];
            $staticText = $formInputs["static_text"];
            $staticFile = $formInputs["static_file"];

            $inputData = CustomizationInput::where(['id' => $inputId])->first();

            if(!$doctor || !$inputData) { continue; }

            $customizedInputType = $inputData->type;
            $doctorInfo = isset($doctor->info) ? $doctor->info : NULL;
            $inputSettings = isset($inputData->settings) ? $inputData->settings : NULL;
            
            $customBoxInner = [];
            $customBoxInner['dimension']['x'] = $inputSettings['dimension']['x'] ?? NULL;
            $customBoxInner['dimension']['y'] = $inputSettings['dimension']['y'] ?? NULL;
            $customBoxInner['dimension']['width'] = $inputSettings['dimension']['width'] ?? NULL;
            $customBoxInner['dimension']['height'] = $inputSettings['dimension']['height'] ?? NULL;
            
            if ($type === 'static') {
                switch ($customizedInputType) {
                    case 'file':
                        $customBoxInner['data']['type'] = 'image';
                        $customBoxInner['data']['url'] = $staticFile;
                        $customBoxInner['data']['x'] = $inputSettings['dimension']['x'] ?? NULL;
                        $customBoxInner['data']['y'] = $inputSettings['dimension']['y'] ?? NULL;
                        $customBoxInner['data']['resize_width'] = $inputSettings['dimension']['width'] ?? NULL;
                        $customBoxInner['data']['resize_height'] = $inputSettings['dimension']['height'] ?? NULL;
                        break;
    
                    case 'text':
                        $customBoxInner['data']['type'] = 'text';
                        $customBoxInner['data']['text'] = $staticText;
                        $customBoxInner['data']['alignment_x'] = $inputSettings['alignment_x'] ?? NULL;
                        $customBoxInner['data']['alignment_y'] = $inputSettings['alignment_y'] ?? NULL;
                        $customBoxInner['data']['color_R'] = $inputSettings['color']['r'] ?? NULL;
                        $customBoxInner['data']['color_G'] = $inputSettings['color']['g'] ?? NULL;
                        $customBoxInner['data']['color_B'] = $inputSettings['color']['b'] ?? NULL;
                        $customBoxInner['data']['font_size'] = $inputSettings['font_size'] ?? NULL;
                        $customBoxInner['data']['font_family'] = 'Calibri.ttf';
                        break;
                    
                    default:
                        break;
                }
            } else if($type === 'form') {
                switch ($customizedInputType) {
                    case 'file':
                        $customBoxInner['data']['type'] = 'image';
                        $customBoxInner['data']['url'] = $doctorInfo[$field] ?? NULL;
                        $customBoxInner['data']['x'] = $inputSettings['dimension']['x'] ?? NULL;
                        $customBoxInner['data']['y'] = $inputSettings['dimension']['y'] ?? NULL;
                        $customBoxInner['data']['resize_width'] = $inputSettings['dimension']['width'] ?? NULL;
                        $customBoxInner['data']['resize_height'] = $inputSettings['dimension']['height'] ?? NULL;
                        break;
    
                    case 'text':
                        $customBoxInner['data']['type'] = 'text';
                        $customBoxInner['data']['text'] = $doctorInfo[$field] ?? NULL;
                        $customBoxInner['data']['alignment_x'] = $inputSettings['alignment_x'] ?? NULL;
                        $customBoxInner['data']['alignment_y'] = $inputSettings['alignment_y'] ?? NULL;
                        $customBoxInner['data']['color_R'] = $inputSettings['color']['r'] ?? NULL;
                        $customBoxInner['data']['color_G'] = $inputSettings['color']['g'] ?? NULL;
                        $customBoxInner['data']['color_B'] = $inputSettings['color']['b'] ?? NULL;
                        $customBoxInner['data']['font_size'] = $inputSettings['font_size'] ?? NULL;
                        $customBoxInner['data']['font_family'] = 'Calibri.ttf';
                        break;
                    
                    default:
                        break;
                }
            }

            array_push($customBox, $customBoxInner);
        }

        return $customBox;        
    }
}