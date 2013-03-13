<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?//set the access control mode of every activity of every delivery and test processes to the new mode "role restricted user delivery"error_reporting(E_ALL);Bootstrap::loadConstants('taoDelivery');Bootstrap::loadConstants('taoTests');Bootstrap::loadConstants('wfEngine');//need to set the persistence mode to "smooth" because the other mode is not implemented before v2.1!if(!core_kernel_persistence_PersistenceProxy::isForcedMode(PERSISTENCE_SMOOTH)){		trigger_error("cannot force smooth mode", E_USER_WARNING);	}else{		//log generis system user:	core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);	$deliveryAuthoring = taoDelivery_models_classes_DeliveryAuthoringService::singleton();	$deliveryClass = new core_kernel_classes_Class(TAO_DELIVERY_CLASS);	$propDeliveryProcess = new core_kernel_classes_Property(TAO_DELIVERY_PROCESS);	$propActivitiesACLmode = new core_kernel_classes_Property(PROPERTY_ACTIVITIES_ACL_MODE);	$deliveryACLmode = new core_kernel_classes_Resource(INSTANCE_ACL_ROLE_RESTRICTED_USER_DELIVERY);	//edit the access control mode of each activity of all delivery processes (compiled delivery) to the new one	foreach($deliveryClass->getInstances(true) as $delivery){		$deliveryProcess = $delivery->getOnePropertyValue($propDeliveryProcess);		if(!is_null($deliveryProcess)){			foreach($deliveryAuthoring->getActivitiesByProcess($deliveryProcess) as $activity){				$activity->editPropertyValues($propActivitiesACLmode, $deliveryACLmode->uriResource);			}		}	}	//edit the access control mode of each activity (item) of all tests	$testClass = new core_kernel_classes_Class(TAO_TEST_CLASS);	$propTestContent = new core_kernel_classes_Property(TEST_TESTCONTENT_PROP);	foreach($testClass->getInstances(true) as $test){		$testContent = $test->getOnePropertyValue($propTestContent);		if(!is_null($testContent) && $testContent instanceof core_kernel_classes_Resource){			foreach($deliveryAuthoring->getActivitiesByProcess($testContent) as $activity){				$activity->editPropertyValues($propActivitiesACLmode, $deliveryACLmode->uriResource);			}		}	}}?>