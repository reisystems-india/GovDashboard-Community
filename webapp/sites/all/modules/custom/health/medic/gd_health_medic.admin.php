<?php


function gd_health_medic_admin_page () {

    drupal_add_js(drupal_get_path('module', 'gd_health_medic') .'/js/scanner.js');

    $medic = new \GD\Health\Medic\Medic();

    $output = '';

    $output .= '<h2>Symptom Scanner</h2>';

    if ( !$medic->getSymptoms() ) {
        $output .= '<div class="alert alert-error" role="alert">There are no symptoms to diagnose.</div>';
        return $output;
    }

    $output .= '<form method="get" action="" class="form-inline">';
    $output .= '<label>Symptoms: </label> ';
    $output .= '<select class="form-control" name="symptom">';
    foreach ( $medic->getSymptoms() as $symptomId => $symptom ) {
        $output .= '  <option value="'.$symptomId.'" '.((isset($_GET['symptom']) && $_GET['symptom']==$symptomId)?'selected="selected"':'').'>'.$symptom->getName().'</option>';
    }
    $output .= '</select>';
    $output .= ' <input type="submit" class="btn btn-primary" value="Scan" />';
    $output .= '</form>';


    // results
    if ( isset($_GET['symptom']) ) {
        $affected = $medic->getAffected($_GET['symptom']);

        $output .= '<h3>Scan Results</h3>';
        if ( !empty($affected) ) {

            $output .= '<div class="alert alert-success" role="alert"><strong>Scan Completed!</strong> Found <strong>'.count($affected).'</strong> patients exhibiting symptom "'.$medic->getSymptom($_GET['symptom'])->getName().'"</div>';

            $allTreatments = array();

            $rows = array();
            foreach ( $affected as $patient ) {
                $row = array();
                $row[] = '<pre>'.print_r($patient['info'],true).'</pre>';
                $row[] = $patient['notes'];

                if ( !empty($patient['treatments']) ) {
                    $markup = '';
                    foreach ($patient['treatments'] as $treatmentName) {
                        $treatment = $medic->getTreatment($treatmentName);

                        if ( !isset($allTreatments[$treatmentName]) ) {
                            $allTreatments[$treatmentName] = $treatment;
                        }

                        $markup .= '<div class="panel panel-info">';
                        $markup .= '  <div class="panel-heading">';
                        $markup .= '    <h3 class="panel-title">' . $treatment->getName() . '</h3>';
                        $markup .= '  </div>';
                        $markup .= '  <div class="panel-body">';
                        $markup .= '    <div class="treatment-description">'.$treatment->getDescription().'</div>';
                        $markup .= '    <div class="treatment-message"></div>';
                        $markup .= '    <div class="treatment-actions text-right"><button type="button" class="treatmentApplyAction btn btn-info btn-sm" data-affected=\'' . json_encode($patient['info']) . '\' data-treatment="' . $treatmentName . '" data-loading-text="Applying...">Apply</button></div>';
                        $markup .= '  </div>';
                        $markup .= '</div>';

                    }
                    $row[] = $markup;
                } else {
                    $row[] = 'None Available';
                }

                $rows[] = $row;
            }

            $output .= '<h4>Treat All Patients</h4>';
            if ( !empty($allTreatments) ) {
                foreach ($allTreatments as $treatmentName => $treatment) {
                    $markup  = '<div class="panel panel-info">';
                    $markup .= '  <div class="panel-heading">';
                    $markup .= '    <h3 class="panel-title">' . $treatment->getName() . '</h3>';
                    $markup .= '  </div>';
                    $markup .= '  <div class="panel-body">';
                    $markup .= '    <div class="treatment-all-description">'.$treatment->getDescription().'</div>';
                    $markup .= '    <div class="treatment-all-message"></div>';
                    $markup .= '    <div class="treatment-all-actions text-right"><button type="button" class="treatmentApplyAllAction btn btn-info btn-sm" data-treatment="' . $treatmentName . '" data-loading-text="Applying...">Apply to All</button></div>';
                    $markup .= '  </div>';
                    $markup .= '</div>';

                    $output .= PHP_EOL.$markup;
                }
            } else {
                $output .= '<div class="alert alert-info" role="alert">No treatments available.</div>';
            }

            $output .= '<h4>Patients</h4>';
            $header = array(array('data'=>'Patient'),array('data'=>'Notes'),array('data'=>'Treatments'));
            $output .= theme('table',array('header'=>$header,'rows'=>$rows));




        } else {
            $output .= '<div class="alert alert-info" role="alert"><strong>Scan Completed!</strong> There are no patients exhibiting symptom "'.$medic->getSymptom($_GET['symptom'])->getName().'"</div>';
        }
    }

    return $output;
}

function gd_health_medic_admin_api_treatment () {
    try {
        $response = 'OK';

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET' :
                break;

            case 'POST' :
                gd_health_medic_treatment_apply(json_decode($_POST['affected']), $_POST['treatment']);
                break;

            default:
                throw new Exception('Unsupported request method.');
        }
    } catch ( Exception $e ) {
        drupal_add_http_header('Status', '500 System Error');
        gd_get_session_messages();
        LogHelper::log_error($e);
        $response = $e->getMessage();
    }

    echo json_encode($response);
    drupal_exit();
}