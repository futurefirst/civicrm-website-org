<?php

/**
 * CustomDataImprovements.Run API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_data_model_improvements_run_spec(&$spec) {
}

/**
 * CustomDataImprovements.Run API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_data_model_improvements_run($params) {

    switch($params['set']){
        case 'custom-data-cleanup':
            DataModelImprovements_CustomDataCleanup();
            break;
        case 'pre-webform-reg-site':
            DataModelImprovements_preWebformRegSite();
            break;
    
    }
}

function DataModelImprovements_CustomDataCleanup(){
    echo "Community profile custom data set should only apply to individuals\n";
    DataModelImprovements_Change_Entity(11,'Individual');
    echo "Community profile custom data set should only apply to individuals\n";
    DataModelImprovements_Change_Entity(8,'Individual');
    echo "People that have filled in the site registration should be classed as End Users\n";
    $query = "UPDATE civicrm_contact AS cc
        JOIN civicrm_value_civicrm_site_registration_4 AS cd ON cd.entity_id = cc.id
        SET contact_sub_type = 'Service_providerEnd_user'
        WHERE contact_sub_type ='Service_provider'";
    CRM_Core_DAO::singleValueQuery($query);
    $query = "UPDATE civicrm_contact AS cc
        JOIN civicrm_value_civicrm_site_registration_4 AS cd ON cd.entity_id = cc.id
        SET contact_sub_type = 'End_user'
        WHERE contact_sub_type =''";
    CRM_Core_DAO::singleValueQuery($query);
    echo "End user organisation data should only apply to Organisations with contact type End user";
    $query = "UPDATE co_civicrm.civicrm_custom_group SET extends_entity_column_value = 'End_user' WHERE civicrm_custom_group.id =3";
    CRM_Core_DAO::singleValueQuery($query);
}

function DataModelImprovements_Change_Entity($custom_group_id, $contact_type){
    $tableName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $custom_group_id, 'table_name');
    CRM_Core_DAO::dropTriggers($tableName);
    echo "- Delete custom data set with id $custom_group_id for organistions that are not of type '$contact_type'\n";
    $query = "DELETE cd
        FROM $tableName AS cd
        JOIN civicrm_contact AS c ON c.id=cd.entity_id
        WHERE c.contact_type!='$contact_type'";
    CRM_Core_DAO::singleValueQuery($query);
    echo "- Set the custom data group to only extend individuals\n";
    $query = "
        UPDATE civicrm_custom_group
        SET extends = '$contact_type'
        WHERE id = $custom_group_id";
    CRM_Core_DAO::singleValueQuery($query);
    CRM_Core_DAO::triggerRebuild($tableName);
}

function DataModelImprovements_preWebformRegSite(){

    //0. Update all organistions to end users where the contact subtype is NULL and they are in the Org group
    $query = "UPDATE civicrm_contact AS cc
        JOIN civicrm_group_contact AS cgc ON cgc.contact_id = cc.id AND cgc.group_id=15
        SET contact_sub_type = 'End_user'
        WHERE contact_sub_type IS NULL";
    CRM_Core_DAO::singleValueQuery($query);
    //1. Remove all organisations that are just service providers from the list of registered sites
    
    //a. mark all ServiceProvider EndUsers as actual ServiceProvider
    $query = "UPDATE civicrm_contact AS cc
        JOIN civicrm_value_civicrm_site_registration_4 AS cd ON cd.entity_id = cc.id
        SET contact_sub_type ='Service_provider'
        WHERE contact_sub_type = 'Service_providerEnd_user'";
    $result = CRM_Core_DAO::ExecuteQuery($query);

    //b. Then delete the contact_group records for all contacts in the group with id 15
    $query = "DELETE cgc
        FROM civicrm_contact AS cc
        JOIN civicrm_group_contact AS cgc
        ON cc.id=cgc.contact_id
        WHERE group_id=15 AND contact_sub_type LIKE '%Service%'";
    $result = CRM_Core_DAO::ExecuteQuery($query);
    
    // 2. Create registered site relationships for all organisations in the registered group
    
    // a. For all orgs that have exactly one employee, assume this is the person that entered the site
    $query = "
        INSERT INTO civicrm_relationship(
        SELECT
            NULL,
            cr.contact_id_a,
            cr.contact_id_b,
            12,
            NULL,
            NULL,
            1,
            NULL,
            1,
            0,
            NULL
        FROM civicrm_group_contact AS cgc
        JOIN civicrm_relationship AS cr ON cr.contact_id_b = cgc.contact_id AND group_id=15 AND relationship_type_id=4 AND is_active
        LEFT JOIN civicrm_relationship AS cra ON cr.contact_id_a=cra.contact_id_a AND cr.contact_id_b=cra.contact_id_b AND cra.relationship_type_id=12
        WHERE cra.id IS NULL
        GROUP BY contact_id
        HAVING count(*)=1
    )";
    $result = CRM_Core_DAO::ExecuteQuery($query);

    // b. For all orgs where there is more than one individual, see if one of those is in the registered sites individuals

    //Create a registered by relationship for the most likely candidate for all orgs with more than one employee
    $query = "
        SELECT
            cr.contact_id_b as id
        FROM civicrm_group_contact AS cgc
        JOIN civicrm_relationship AS cr
            ON cr.contact_id_b = cgc.contact_id AND group_id=15 AND relationship_type_id=4 AND is_active
        LEFT JOIN civicrm_relationship AS cra ON cr.contact_id_a=cra.contact_id_a AND cr.contact_id_b=cra.contact_id_b AND cra.relationship_type_id=12
        WHERE cra.id IS NULL
        GROUP BY contact_id
        HAVING count( * ) >1
        ";
    
    $org = CRM_Core_DAO::ExecuteQuery($query);
    while($org->fetch()){
        $query = "SELECT cc.id FROM
            civicrm_contact AS cc JOIN
            civicrm_group_contact AS cgc ON cc.id=cgc.contact_id AND group_id=16 AND employer_id={$org->id}
            ";
        $ind = CRM_Core_DAO::ExecuteQuery($query);

        if($ind->N==1){
            $ind->fetch();
            echo $org->id.' '.$ind->N.', ';
            $params = array(
                'version' => 3,
                'sequential' => 1,
                'contact_id_a' => $ind->id,
                'contact_id_b' => $org->id,
                'relationship_type_id' => 12,
            );
        $result = civicrm_api('Relationship', 'create', $params);
        print_r($params);
        print_r($result);
        }else{
            echo $org->id.' '.$ind->N.', ';
        }
    }

    //ensure all registered by relationships are permissioned
//     SELECT
// cc.display_name, count(activity_id)
// FROM civicrm_contact AS cc
// JOIN civicrm_activity_contact AS cac
// 	ON cc.id=cac.contact_id
// WHERE cc.employer_id=???
// GROUP BY cc.id

    // record activities for all organisations in the registered sites group that don't have that type of activity recorded and assign to the individual that registered the site as well

    return;
    
}
