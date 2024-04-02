<?php

use App\Library\Services\PublicTools;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::prefix('sign')->group(function () {
    Route::get('/contract/{contract_id}/{render_type}', 'SignController@getDocument');
    Route::get('/sendAgreements', 'SignController@sendAgreements');
});

Route::get('/', 'PagesController@index')->middleware(['auth'])->name('dashboard');
Route::get('/send-markdown-mail', 'AdminController@sendOfferMail');
Route::get('/send-task-mail', 'TaskController@sendTaskMail');
Route::get('/fix/entities-ref', 'ClientController@fixEntitiesRef');

Route::prefix('artisan')->group(function () {
    Route::get('/migrate', function () {
        $status = Artisan::call('migrate');
        return '<h1>Migration executed</h1>';
    });
    Route::get('/cache/clear', function () {
        $status = Artisan::call('cache:clear');
        return '<h1>Cache cleared</h1>';
    });
});

Route::prefix('backup')->group(function () {
    Route::get('/fixentities/refs/{ref}', 'BackupController@fixUpdateRefEntities');
    Route::get('/update/entities/codes', 'BackupController@updateEntitiesCollectiveAndAuxiliaryCustomerAccountCodes');
    Route::get('/test', 'BackupController@test');
    Route::get('/fix/contacts', 'BackupController@fixContactsFirstnameLastname');
    Route::prefix('data')->group(function () {
        Route::get('/import', 'BackupController@importDemoDatas');
        Route::get('/export', 'BackupController@exportDemoDatas');
        Route::get('/clear', 'BackupController@clearDemoDatas');
        Route::get('/import/entities/s', 'BackupController@importEntities');
        Route::get('/import/entities/p', 'BackupController@importParticularsEntities');
        Route::get('/import/contacts/p', 'BackupController@importParticularsContacts');
        Route::get('/import/intervenants/external', 'BackupController@importExternalIntervenants');
        Route::get('/import/intervenants/internal', 'BackupController@importInternalIntervenants');
        Route::get('/import/entities/contact', 'BackupController@importEntityContactAddressEnrollmentMembers');
        //backup/data/manage/duplicate/entities/1
        Route::get('/manage/duplicate/entities/{case}', 'BackupController@manageDuplicateEntities');
    });
});


/* Route::middleware(['EnsureUserHasProfilRole:APPRENANT'])->group(function () {
    Route::get('/users', 'UserController@index');
}); */

// Route::middleware(['EnsureUserHasProfilRole:CRFPE,ADMIN'])->group(function () {

// Route::middleware(['EnsureUserHasProfilRole:FORMATEUR,FORMATEUR'])->group(function () {

// Route::group(['middlewareFrm' => ['EnsureUserHasProfilRole:FORMATEUR,FORMATEUR']], function () {

    /** Start Task Part **/
    // get chart data json
    Route::get('/getTicketsTerminerParContact', 'TaskController@getTicketsTerminerParContact')->middleware(['auth']);
    Route::get('/getTicketsNonTerminerParContact', 'TaskController@getTicketsNonTerminerParContact')->middleware(['auth']);
    Route::get('/getTicketsNonTerminer', 'TaskController@getTicketsNonTerminer')->middleware(['auth']);
    Route::get('/getnewpassword', 'Auth\PasswordResetLinkController@getnewpass')->middleware(['auth']);
    Route::post('/updatepass', 'Auth\PasswordResetLinkController@updatenewpass');
    Route::post('/resetpassword', 'Auth\PasswordResetLinkController@resetpassword');
    Route::post('/generatepass', 'Auth\PasswordResetLinkController@generatepass');
    Route::get('/getnewmdp', 'Auth\PasswordResetLinkController@getnewmdp');
    Route::get('/addtask', 'TaskController@addTask')->middleware(['auth']);
    Route::get('/gettasks', 'TaskController@getTasks')->middleware(['auth']);
    Route::get('/getstatustasks', 'TaskController@getstatustasks')->middleware(['auth']);
    Route::get('/getSource', 'TaskController@getSource')->middleware(['auth']);
    Route::get('/getType', 'TaskController@getType')->middleware(['auth']);
    Route::get('/getResponse', 'TaskController@getResponseMode')->middleware(['auth']);
    Route::get('/getObject', 'TaskController@getObject')->middleware(['auth']);
    Route::get('/getEtat', 'TaskController@getEtat')->middleware(['auth']);
    Route::post('/updateComment', 'TaskController@validateComment')->middleware(['auth']);
    Route::get('/annulateTask/{row_id}', 'TaskController@annulateTask')->middleware(['auth']);
    Route::get('/terminateTask/{row_id}', 'TaskController@terminateTask')->middleware(['auth']);
    Route::post('/reportTask', 'TaskController@reportTask')->middleware(['auth']);
    Route::post('/transfertTask', 'TaskController@transfertTask')->middleware(['auth']);
    Route::post('/newTask', 'TaskController@validateTask')->middleware(['auth']);
    Route::post('/createTask/{parent_id?}', 'TaskController@createTask')->middleware(['auth']);
    Route::post('/upload', 'TaskController@upload')->middleware(['auth'])->name('upload');


    Route::post('/update/subtask', 'TaskController@UpdateSubTask')->middleware(['auth'])->name('update_subtask');
    /** End of Task Part **/

    Route::get('/formations', 'FormationController@index');
    Route::get('/users', 'UserController@index');
    Route::get('/clients', 'ClientController@list');
    Route::get('/contacts/{contact_id?}', 'ClientController@listContacts');
    Route::get('/afs', 'ActionFormationController@list');
    // Pré planifications
    Route::get('/list/planifications', 'CalendarController@produitFormation')->middleware(['auth']);
    Route::get('/view/planification/{planification_id}', 'CalendarController@index')->middleware(['auth']);
    Route::post('/calendar', 'CalendarController@store')->middleware(['auth']);
    Route::post('/calendar/duplicate', 'CalendarController@duplidateEvent')->middleware(['auth']);
    Route::patch('/calendar/update/{id}', 'CalendarController@update')->middleware(['auth']);
    Route::patch('/calendar/dropUpdate/{id}', 'CalendarController@dropUpdate')->middleware(['auth']);
    Route::delete('/calendar/destroy/{id}', 'CalendarController@destroy')->middleware(['auth']);


    Route::get('/devis', 'CommerceController@estimates');
    Route::get('/agreements', 'CommerceController@agreements');
    Route::get('/invoices', 'CommerceController@invoices');
    Route::get('/avoirs', 'CommerceController@avoirs');
    Route::get('/sessions', 'ActionFormationController@listSessions');
    Route::get('/convocations', 'CommerceController@convocations');
    Route::group(['middleware' => ['isFormatteur', 'isEtudiant']], function() {
        Route::get('/agendafrm', 'AdminController@scheduleRroomsfrm');
    });
    Route::get('/agenda', 'AdminController@scheduleRrooms')->middleware('isAdmin');
    Route::get('/payments', 'AdminController@payments');
    Route::get('/contrats-intervenants', 'AdminController@contratsIntervenants');
    Route::get('/stages', 'StageController@stages');
    Route::get('/presences', 'PresencesController@presences');
    Route::get('/import', 'ImportController@import');
    Route::get('/control/invoices', 'CommerceController@controleinvoices');
    Route::get('view/subtask/getEntities', 'ContactController@getEntities')->middleware(['auth']);
    Route::get('view/edit/getEntities', 'ContactController@getEntities')->middleware(['auth']);
    // TDB Export brut
    Route::get('/tdb', 'ExportController@getTDB')->middleware(['auth']); ;
    Route::get('/TDBIntervenants', 'ExportController@TDBIntervenants')->middleware(['auth']); ;

    Route::prefix('api')->group(function () {
        Route::get('/select/options/getcontactswithnousers', 'ContactController@getContactsWithNoUsers');
        Route::get('/select/options/getcontacts/{entity_id?}', 'ContactController@getContacts');
        Route::get('/select/options/getEntities', 'ContactController@getEntities');
        Route::get('/select/options/getcontact/{id}', 'ContactController@getContact');
        Route::get('/getcontactdata/{id}', 'ContactController@getContactData');
        //test contact stats
        Route::get('/select/options/getStatscontact/{id}', 'ContactController@getStatsContact');
        Route::get('/select/options/getStatsTable', 'ContactController@getStatsTable');

        Route::get('/download/zip/invoices', 'CommerceController@downloadZipInvoices');
        Route::get('/send/emails/invoices', 'CommerceController@sendEmailsInvoices');
        Route::get('/merge/pdf/invoices', 'CommerceController@mergePdfsInvoices');
        Route::prefix('export')->group(function () {
            Route::post('/invoices', 'ExportController@invoicesExport');
            Route::post('/avoirs', 'ExportController@avoirsExport');
        });
        // proposals export
        Route::get('/download/zip/proposals', 'StageController@downloadZipProposals');
        Route::get('/merge/pdf/proposals', 'StageController@mergePdfsProposals');
        // Route::prefix('export')->group(function () {
        //     Route::post('/proposals', 'ExportController@proposalsExport');
        // });
        Route::post('/sendemail/validated/contracts/schedulecontacts', 'AdminController@sendemailValidateContractScheduleContacts');
        Route::post('/validate/contract/schedulecontacts', 'ActionFormationController@validateContractScheduleContacts');
        Route::post('/validate/contract/deleteschedulecontacts', 'ActionFormationController@deleteContractScheduleContacts');
        Route::get('/generate/certificates/{af_id}', 'ActionFormationController@generateCertificatesFromAf');
        Route::get('/generate/certificates/{af_id}/student', 'ActionFormationController@generateStudentsCertificatesFromAf');
        Route::prefix('sdt')->group(function () {
            Route::post('/ModifyTask', 'TaskController@ModifyTask');
            Route::post('/addcomments', 'TaskController@createComment');

            Route::post('/entitydocuments/{entity_id}', 'ClientController@sdtEntityDocuments');

            Route::post('/studentstatus/{member_id}', 'ActionFormationController@sdtStudentStatus');
            Route::post('/students', 'ActionFormationController@sdtStudents');
            Route::post('/formerswithoutcontracts', 'AdminController@sdtFormersWithoutContracts');
            Route::post('/certificate/attached/documents/{certificate_id}', 'CommerceController@sdtCertificateAttachedDocuments');

            Route::post('/contract/attached/documents/{contract_id}', 'CommerceController@sdtContractAttachedDocuments');

            Route::post('/certificates/{af_id}/{type?}', 'CommerceController@sdtCertificates');
            Route::get('/file/contacts/{file_id}', 'importController@listContacts');
            Route::get('/client/export', 'ExportController@exportClients');
            // export tasks
            Route::get('/tasks/export', 'ExportController@exportTasks');
            Route::get('/tasksevolution/export', 'ExportController@exportTasksEvolution');
            Route::get('/deepsearch/entities', 'ContactController@entitiesDeepSearch');
            Route::get('/deepsearch/entities/load', 'ContactController@loadDeepSearchEntities');
            // export TDB
            Route::get('/tdb/exportIntervenants', 'ExportController@tdbExportIntervenants');
            Route::get('/tdb/exportActivites', 'ExportController@tdbExportActivites');
            Route::get('/tdb/exportEtudiants', 'ExportController@tdbExportEtudiants');
            Route::get('/personne/export', 'ExportController@personnesExport');
            Route::post('/formations', 'FormationController@sdtFormations');
            Route::post('/sheets/{formation_id}', 'FormationController@sdtSheetsFormation');
            Route::post('/logs', 'AdminController@sdtLogs');
            Route::post('/entities', 'ClientController@sdtEntities');
            Route::post('/contacts/{entity_id}', 'ClientController@sdtContacts');
            Route::post('/member/contact/{af_id}/{group_id}', 'ClientController@sdtContactsMember');
            Route::post('/adresses/{entity_id}', 'ClientController@sdtAdresses');
            Route::post('/sessions/{action_id}', 'ActionFormationController@sdtSessions');
            Route::post('/users', 'UserController@sdtUsers');
            Route::post('/params', 'AdminController@sdtParams');
            Route::post('/afs', 'ActionFormationController@sdtAfs');
            Route::post('/tasks', 'TaskController@sdtTasks');
            Route::get('/listAfs', 'ActionFormationController@getAfs');
            Route::get('/listPfs', 'ActionFormationController@getPfs');
            Route::post('/ptemplates', 'AdminController@sdtPtemplates');
            Route::post('/enrollments/{af_id}/{enrollment_type}', 'ActionFormationController@sdtEnrollments');
            Route::post('/createAccounts', 'ActionFormationController@createAccountByAf');
            Route::post('/select/contacts/{entity_id}/{enrollment_id}/{is_former}', 'ClientController@sdtSelectContacts');
            Route::post('/select/members/{af_id}/{enrollment_type}', 'ActionFormationController@sdtSelectMembers');
            Route::post('/select/sessions/members/{af_id}/{enrollment_type}', 'ActionFormationController@sdtSelectSessionsMembers');
            Route::post('/select/registrants/{enrollment_id}', 'ActionFormationController@sdtRegistrants');
            Route::post('/select/sdtsubtasks/{taskid}', 'TaskController@sdtSubTasks');
            Route::post('/prices', 'AdminController@sdtPrices');
            Route::post('/prices/pf/{pf_id}', 'FormationController@sdtPricesPf');
            Route::post('/select/prices/{pf_id}/{af_id}', 'AdminController@sdtSelectPrices');
            Route::post('/prices/af/{af_id}', 'ActionFormationController@sdtPricesAf');
            Route::post('/enrollmentsmembers/{af_id}', 'ActionFormationController@sdtEnrollmentsMembers');
            Route::post('/ressources', 'AdminController@sdtRessources');
            Route::post('/select/ressources/{ressource_type}', 'AdminController@sdtSelectRessources');
            Route::post('/contracts/{af_id}', 'ActionFormationController@sdtContracts');
            Route::get('/agreement_fact/{af_id}', 'CommerceController@sdtAgreementFact');
            Route::get('/invoice_fact/{af_id}', 'CommerceController@sdtInvoiveFact');
            //Route::post('/documents', 'AdminController@sdtDocuments');
            Route::post('/historique/af/{af_id}', 'ActionFormationController@sdtAfLogs');
            Route::post('/historique/pf/{pf_id}', 'FormationController@sdtPfLogs');
            Route::post('/estimates/{af_id}', 'CommerceController@sdtEstimates');
            Route::post('/agreements/{af_id}', 'CommerceController@sdtAgreements');
            Route::post('/invoices/{af_id}', 'CommerceController@sdtInvoices');
            Route::post('/avoirs/{af_id}', 'CommerceController@sdtAvoirs');
            Route::post('/planifications', 'CalendarController@sdtPlanifications');

            Route::get('/estimates_fact/{af_id}', 'CommerceController@sdtestimatesfact');

            // Route::post('/sdt_intervenants', 'CalendarController@sdtIntervenants');
            Route::post('/groups/{af_id}', 'ClientController@sdtGroups');
            Route::post('/groupments/{af_id}', 'ClientController@sdtGroupments');
            Route::post('/convocations/{af_id}', 'CommerceController@sdtConvocations');
            Route::post('/membersconvocation/{af_id}', 'CommerceController@sdtEnrollmentsMembersConvocation');
            Route::post('/seancesconvocation/{session_id}', 'CommerceController@sdtEnrollmentsSeancesConvocation');
            Route::post('/getdatesconvocation/{session_id}', 'CommerceController@sdtEnrollmentsGetDatesConvocation');
            Route::post('/getseancesconvocation/{id_sessiondate}', 'CommerceController@sdtEnrollmentsGetSeancesConvocation');
            Route::post('/models/{model_id}', 'AdminController@sdtModels');
            Route::post('/select/pf_formation/to/sessions/{pf_id}', 'ActionFormationController@sdtPfFormationsToSessions');
            Route::post('/select/groups/{af_id}/{groupment_id}', 'ClientController@sdtSelectGroupment');
            Route::post('/agenda/{start_date}/{nb_days}', 'AdminController@sdtScheduleRrooms')->middleware('isAdmin');
            Route::group(['middleware' => ['isFormatteur', 'isEtudiant']], function() {
                Route::post('/agenda1/{start_date}/{nb_days}', 'AdminController@sdtScheduleRroomsFrm');
            });
            Route::post('/controle/contracts', 'AdminController@sdtContractsControle');
            Route::post('/stages/{af_id}', 'StageController@sdtStages');
            Route::post('/stages/proposals/{af_id}', 'StageController@sdtStagesProposals');
            Route::post('/controlinvoices', 'CommerceController@sdtControlInvoices');
            Route::post('/indexes', 'AdminController@sdtIndexes');
        });
        Route::get('/default/sheet/{formation_id}', 'FormationController@defaultSheetFormation');
        Route::get('/code/formation/{formation_id}/{categorie_id}', 'FormationController@generateCodeForFormation');
        Route::get('/catalogues/{categorie_id}/{with_trashed}', 'CatalogueController@srcCatalogues');
        Route::get('/structures/{structure_id}/{with_trashed}', 'AdminController@srcStructures');
        Route::get('/filter/catalogues', 'CatalogueController@jsonCataloguesForFilter');
        Route::post('/code/categorie', 'CatalogueController@generateCodeForCategorie');
        Route::get('/order/categorie/{categorie_parent_id}', 'CatalogueController@generateOrderShowForCategorie');
        Route::get('/generate/password', 'UserController@generateRandomPassword');
        Route::get('/generate/login/{user_id}', 'UserController@generateRandomLogin');
        Route::get('/tree/schedules/{af_id}/{mode}', 'ActionFormationController@treeSchedulesContacts');
        Route::get('/tree/schedules/session/{session_id}/{mode}', 'ActionFormationController@treeSchedulesContactSession');
        Route::get('/tree/schedules/ressources/{af_id}/{mode}', 'ActionFormationController@treeSchedulesRessources');
        Route::get('/tree/schedules/formers/{af_id}/{member_id}', 'ActionFormationController@treeSchedulesFormers');

        Route::get('/tree/schedules/formers/contract/{af_id}/{member_id}', 'ActionFormationController@treeSchedulesFormersContract');
        Route::get('/tree/schedules/contactformers/contract/{af_id}/{contact_id}', 'ActionFormationController@treeSchedulesFormersContractByContact');

        Route::get('/restore/document/{document_model_id}', 'AdminController@restoreDocument');
        Route::get('/restore/email/{document_model_id}', 'TaskController@restoreEmail');
        Route::get('/generate/agreement/{estimate_id}', 'CommerceController@generateAgreement');

        Route::post('/tree/schedules/{af_id}/{mode}', 'ActionFormationController@treeSchedulesContactsFilter');
        Route::post('/presences/schedules/{membre_id}', 'presencesController@generateListAf');
        Route::post('/presences/schedules/export/{membre_id}', 'presencesController@exportListAf');
        Route::get('/presences/schedules/exportget', 'presencesController@getexportListAf');
        Route::post('/presences/editstate/{membre_id}', 'PresencesController@updatestate');
        // download excel file
        Route::get('/presences/downloadexcelfile', 'presencesController@downloadexcelfile')->name('presences.downloadexcelfile');

        Route::prefix('suggested')->group(function () {
            Route::get('/select/{contactId}/{attachment}', 'ImportController@selectSuggested');
            Route::get('/select/prospect/{contactId}/{attachment}', 'ImportController@selectSuggestedProspect');
            // Route::get('/{contact}/{attachment}', 'ImportController@showSuggested');
        });
        Route::prefix('delete')->group(function () {
            Route::get('/formation/{formation_id}', 'FormationController@deleteFormation');
            Route::get('/planification/{planification_id}', 'CalendarController@deletePlanification');
            Route::get('/sheet/{sheet_id}', 'FormationController@deleteSheet');
            Route::get('/categorie/{categorie_id}', 'CatalogueController@deleteCategorie');
            Route::get('/enrollment/{enrollment_id}', 'ActionFormationController@deleteEnrollment');
            Route::get('/member/{member_id}', 'ActionFormationController@deleteMember');
            Route::get('/schedulecontact/{schedulecontact_id}', 'ActionFormationController@deleteScheduleContact');
            Route::get('/schedulegroup/{schedulecontact_id}/{schedulegroup_id}', 'ActionFormationController@deleteScheduleGroup');
            Route::get('/scheduleressource/{scheduleressource_id}', 'ActionFormationController@deleteScheduleRessource');
            Route::delete('/contract', 'ActionFormationController@deleteContract');
            Route::delete('/intervenant', 'ActionFormationController@deleteIntervenantwithoutcontract');
            Route::delete('/funding', 'CommerceController@deleteFunding');
            Route::delete('/fundingpayment', 'CommerceController@deleteFundingPayment');
            Route::delete('/session', 'ActionFormationController@deleteSession');
            Route::delete('/sessiondate', 'ActionFormationController@deleteSessionDate');
            Route::delete('/afrelprice', 'ActionFormationController@deleteAfRelPrice');
            Route::delete('/pfrelprice', 'FormationController@deletePfRelPrice');
            Route::delete('/item/invoice', 'CommerceController@deleteItemInvoice');
            Route::delete('/gedattachment', 'ImportController@deleteGedAttachment');
            Route::delete('/docs/estimatesfact/{estimate_id}/{af_id}', 'CommerceController@deleteEstimatesfact');
            Route::delete('/docs/agreement/{agreement_id}/{af_id}', 'CommerceController@deleteAgreement');
            Route::delete('/docs/invoice/{invoice_id}/{af_id}', 'CommerceController@deleteInvoice');
        });
        Route::prefix('archive')->group(function () {
            Route::get('/formation/{formation_id}', 'FormationController@archiveFormation');
            Route::post('/categorie', 'CatalogueController@archiveCategorie');
        });
        Route::prefix('unarchive')->group(function () {
            Route::get('/categorie/{categorie_id}', 'CatalogueController@unarchiveCategorie');
        });
        Route::get('/select/options/files', 'ImportController@getFileImport');
        Route::get('/select/options/files/contact/{file_id}', 'ImportController@getContactFileImport');
        Route::post('/select/options', 'AdminController@selectOptions');
        Route::get('/select/options/entities/{entity_id}/{entity_type}/{is_former}', 'AdminController@selectEntitiesOptions');
        Route::get('/select/options/models/{model_id}', 'AdminController@selectModelsOptions');
        Route::get('/select/options/parents/{parent_id}', 'AdminController@selectParentsOptions');
        Route::get('/select/options/formations/{autorize_af}', 'AdminController@selectFormationsOptions');
        Route::post('/move/categorie', 'CatalogueController@moveCategorie');
        Route::post('/insee/siret', 'AdminController@inseeApi');
        Route::get('/geo/cities/{codePostal}', 'AdminController@geoApiSearchCitiesByCodePostal');
        Route::get('/select/options/sessions/{af_id}', 'AdminController@selectSessionsOptions');
        Route::get('/select/options/prices/{af_id}/{entitie_id}', 'AdminController@selectPricesOptions');
        Route::get('/select/options/pricesbytype/{af_id}/{entity_type}', 'AdminController@selectPricesByEntityTypeOptions');
        Route::get('/select/options/prices/formers', 'AdminController@selectPricesOptionsFormers');
        Route::get('/select/options/members/{af_id}', 'ActionFormationController@selectMembersOptions');
        Route::get('/select/options/presences/members/{af_id}/{group_id}', 'ActionFormationController@selectMembersOptionsAfGroup');
        Route::get('/select/options/ressources/{res_id}/{type}', 'AdminController@selectRessourcesOptions');
        Route::get('/select/options/formers/members/{af_id}/{type_former_intervention}/{contact_id}', 'ActionFormationController@selectFormersMembersOptions');
        Route::get('/select/options/formers/contacts/{af_id}/{contact_id}/{type_former_intervention}', 'ActionFormationController@selectFormersContactsOptions');
        Route::get('/check/af/has-unknown-date/{af_id}', 'ActionFormationController@checkAfHasUnkownDate');
        Route::get('/select/options/afs/{af_id}', 'CommerceController@selectAfsOptions');
        Route::get('/select/options/group/ressources/{ressource_id}', 'ClientController@selectRessourcesOptions');
        Route::get('/select/options/afentities/{af_id}/{entity_id}', 'CommerceController@selectEntitiesOptions');
        Route::get('/select/taxes', 'CommerceController@selectTaxes');
        Route::get('/select/options/contacts/{entity_id}', 'CommerceController@selectContactsOptions');
        Route::get('/select/options/funders/entities/{agreement_id}/{funding_id}', 'CommerceController@selectFunderEntitiesOptions');
        Route::get('/select/options/agreements/{af_id}/{entity_id}/{agreement_id}', 'CommerceController@selectAgreementsOptions');
        Route::get('/select/options/entitiesforinvoice/{af_id}', 'CommerceController@selectEntitiesForInvoiceOptions');
        //Route::get('/select/options/contactsforinvoice/{entity_id}', 'CommerceController@selectContactsForInvoiceOptions');
        Route::get('/select/options/entitycontact/{agreement_id}/{type}', 'CommerceController@selectEntityContactOptions');
        Route::get('/select/options/fundings/{agreement_id}', 'CommerceController@selectFundingsOptions');
        Route::get('/select/options/funder/contacts/{entity_id}', 'CommerceController@selectFunderContactsOptions');
        Route::get('/select/options/fundingpayments/{invoice_id}', 'CommerceController@selectFundingpaymentsOptions');
        Route::get('/select/options/deadlines/{entity_id}/{agreement_id}/{mode}', 'CommerceController@selectDeadlinesOptions');
        Route::get('/select/options/mail/contacts/{entity_id}', 'CommerceController@selectMailContactsOptions');
        Route::get('/statistics/pf/{pf_id}', 'StatisticsController@getStatisticsPf');
        Route::get('/statistics/af/{af_id}', 'StatisticsController@getStatisticsAf');
        Route::get('/statistics/dashboard/widgets', 'StatisticsController@getStatisticsDashboardWidgets');
        Route::get('/select/options/entities/type/{entity_type}', 'AdminController@selectEntitiesByTypeOptions');
        Route::get('/select/options/entities/by_type/{entity_type}/{is_former}', 'AdminController@selectEntitiesByTypeStagiaireFormerOptions');
        Route::get('/select/options/products/{pf_id}', 'FormationController@selectProductsOptions');
        Route::get('/select/options/type_products', 'FormationController@selectTypesProductsOptions');
        Route::group(['middleware' => ['isFormatteur', 'isEtudiant']], function() {
            Route::get('/get/agenda/{start_date}/{nb_days}/{af_id}', 'AdminController@getAgenda');
        });
        Route::get('/get/agenda/{start_date}/{nb_days}/{af_id}', 'AdminController@getAgenda')->middleware('isAdmin');
        Route::get('/get/date/{next_previous}/{current_start_date}', 'AdminController@getDateByParamNextPrevious');
        Route::get('/select/options/groups/{af_id}', 'ActionFormationController@selectGroupsOptions');
        //périodes
        Route::get('/select/options/sessions/periods/{session_id}/{af_id}', 'StageController@selectSessionsPeriodsOptions');
        Route::get('/select/options/stagiaires/members/{member_id}/{af_id}', 'StageController@selectStagiairesMembersOptions');
        Route::get('/select/options/stage/entities/{entity_id}', 'StageController@selectStageEntitiesOptions');
        Route::get('/select/options/stage/contacts/{contact_id}/{entity_id}', 'StageController@selectStageContactsOptions');
        Route::get('/select/options/stage/adresses/{adresse_id}/{entity_id}', 'StageController@selectStageAdressesOptions');
        Route::get('/select/options/stage/referance/{membre_id}', 'StageController@selectStageReferanceOptions');
        Route::get('/select/options/studentscontacts/{af_id}', 'CommerceController@getStudentsContactsByAfForSelect');
        Route::get('/select/options/listfundings', 'CommerceController@selectListFundingsOptions');
        Route::get('/select/options/funder/listcontacts/{entity_id}', 'CommerceController@selectFunderListContactsOptions');
        Route::get('/get/entity/collective_code/{entity_id}', 'CommerceController@getEntityCollectiveCode');
    });

    Route::prefix('pdf')->group(function () {
        Route::get('/certificate/{certificate_id}/{render_type}', 'PdfController@createPdfCertificate');
        Route::get('/certificate/{certificate_id}/student/{render_type}', 'PdfController@createStudentsPdfCertificate');
        Route::get('/contract/{contract_id}/{render_type}', 'ActionFormationController@createPdfContract');
        Route::get('/overview/{document_model_id}', 'AdminController@createPdfOverview');
        Route::get('/overviewmail/{document_model_id}', 'TaskController@createEmailOverview');
        Route::get('/estimate/{estimate_id}/{render_type}', 'CommerceController@createPdfEstimate');
        Route::get('/agreement/{agreement_id}/{render_type}', 'CommerceController@createPdfAgreement');
        Route::get('/invoice/{invoice_id}/{render_type}', 'CommerceController@createPdfInvoice');
        Route::get('/transcript/{af_id}/{member_id}/{timestructure_id}', 'ActionFormationController@createPdfTranscript');
        Route::get('/convocation/{convocation_id}/{render_type}', 'PdfController@createPdfConvocation');
        Route::get('/attendance-absence-sheet/{af_id}/{render_type}/{group_id}/{start_date?}/{end_date?}/{session_id?}/{member_id?}/{training_site?}', 'PdfController@createPdfAttendanceAbsenceSheet');
        Route::post('/generate/attendance-absence-sheet', 'PdfController@generatePdfAttendanceAbsenceSheetFromFilter');
        Route::get('/convention/stage/{internshiproposal_id}/{render_type}', 'PdfController@createPdfConventionStage');
        Route::get('/refund/{refund_id}/{render_type}', 'PdfController@createPdfRefund');
        Route::get('/af/technical/sheet/{af_id}/{render_type}', 'PdfController@createPdfAfTechnicalSheet');
        Route::get('/transferCalendar/{preplannings_start_date}/{preplannings_end_date}/{Ppreplanning_id}', 'PdfController@createPdfTransferCalendar');
    });


    Route::prefix('email')->group(function () {
        Route::get('/overviewmail/{document_model_id}', 'TaskController@createEmailOverview');
        Route::get('/sendMailTask/{row_id}', 'TaskController@sendMailTask');
        Route::post('/sendMailTask/{task_id}', 'TaskController@processSendMailTask');
    });
    Route::prefix('list')->group(function () {
        Route::get('/sheets/{formation_id}', 'FormationController@listSheetsFormation');
    });
    Route::prefix('view')->group(function () {
        Route::get('/formation/{id}', 'FormationController@viewFormation');
        Route::get('/sheet/{formation_id}/{sheet_id}', 'FormationController@viewSheet');
        Route::get('/af/sheet/{af_id}', 'ActionFormationController@viewAfSheet');
        Route::get('/entity/{id}', 'ClientController@viewEntity');
        Route::get('/content/construct/{viewtype}/{entity_id}', 'ClientController@constructViewContentEntity');
        Route::get('/af/{id}', 'ActionFormationController@viewAf');
        Route::get('/content/construct/af/{viewtype}/{id}', 'ActionFormationController@constructViewContentAf');
        Route::get('/content/construct/pf/{viewtype}/{id}', 'FormationController@constructViewContentPf');
        Route::get('/import/{viewtype}', 'ImportController@constructViewImport');
        Route::get('/logs/{file_id}', 'ImportController@viewLogs');
        Route::get('/suggested/{attachment}', 'ImportController@showSuggested');

        Route::get('/student/status/{member_id}', 'ActionFormationController@viewStudentStatus');
        Route::get('/student/schedule/{member_id}', 'ActionFormationController@viewStudentSchedule');
        Route::get('/student/cancellation/{member_id}', 'ActionFormationController@viewStudentCancellation');
        Route::get('/task/{id}', 'TaskController@viewTask');
        Route::get('/edit/{id}', 'TaskController@editTask');
        Route::get('/subtask/{id}', 'TaskController@addsubTask');
        Route::get('/addcomment/{id}', 'TaskController@addComment');
        Route::get('/depot/devis/{id}', 'FormationController@getDepotDevis');
        Route::get('/show/devis/{devis_id}/{member_id}/{type}', 'FormationController@getDevis');
        Route::get('/show/motif/devis/{devis_id}/{member_id}/{step}', 'FormationController@getRefusMotif');
        Route::post('/send/motif/devis', 'FormationController@sendRefusDevis')->name('send_motif_devis');
        Route::get('/show/download/document/{id}/{af_id}/{member_id}', 'FormationController@showDownloadDocument')->name('download_document');
        Route::get('/download/document/{path}', 'FormationController@DownloadDocuments')->name('download_documents');

    });

    Route::prefix('form')->group(function () {

        Route::get('/student/status/{member_id}/{student_status_id}', 'ActionFormationController@formStudentStatus');
        Route::post('/student/status', 'ActionFormationController@storeFormStudentStatus');
        Route::delete('/student/status/{student_status_id}', 'ActionFormationController@deleteStudentStatus');

        Route::post('/student/importschedule/{member_id}', 'ActionFormationController@importStudentSchedule');
        Route::post('/student/cancel/{member_id}', 'ActionFormationController@cancelStudent');

        Route::get('/formation/{id}/{type}', 'FormationController@formFormation');
        Route::post('/formation', 'FormationController@storeFormFormation');
        Route::get('/sheet/{formation_id}/{id}', 'FormationController@formSheet');
        Route::post('/sheet', 'FormationController@storeFormSheet');

        Route::get('/categorie/{id}', 'CatalogueController@formCategorie');
        Route::post('/structure', 'AdminController@storeFormStructure');
        Route::get('/structure/{id}', 'AdminController@formStructure');
        Route::post('/model', 'AdminController@storeFormModel');
        Route::get('/model/{id}', 'AdminController@formModel');
        Route::post('/categorie', 'CatalogueController@storeFormCategorie');
        Route::get('/entitie/{entity_id}', 'ClientController@formEntitie');
        Route::get('/construct/{entity_id}/{entity_type}', 'ClientController@constructFormByEntityType');
        Route::post('/entitie', 'ClientController@storeFormEntitie');
        Route::get('/contact/{contact_id}/{entitie_id}', 'ClientController@formContact');
        Route::get('/contact/component/{contact_id}/{entitie_id}', 'ClientController@formContactComponent');
        Route::get('/formFileUpload/{category?}', 'ImportController@getformFileUpload');
        Route::post('/formFileUpload/uploadfile', 'ImportController@postFormFileUpload');
        Route::post('/import/task', 'ImportController@storeImportTask');
        Route::post('/upload/certificate/attached/documents', 'CommerceController@uploadCertificateAttachedDocuments');

        Route::post('/upload/estimates_fact/attached/documents', 'CommerceController@uploadEstimatesFactAttachedDocuments');
        Route::post('/upload/agreement/intfac/attached/documents', 'CommerceController@uploadAgreementIntfacAttachedDocuments');
        Route::post('/upload/invoice/intfac/attached/documents', 'CommerceController@uploadInvoiceIntfacAttachedDocuments');
        Route::get('/validation/estimatesfact/{estimate_id}/{af_id}', 'CommerceController@validationEstimatesfact');
        Route::get('/upload/agreement/attached/documents/{agreement_id}/{af_id}', 'CommerceController@formagreementintfac');
        Route::get('/upload/invoice/attached/documents/{invoice_id}/{af_id}', 'CommerceController@forminvoiceintfac');
        Route::post('/validationform/estimatesfact/{estimate_id}/{af_id}', 'CommerceController@validationformEstimatesfact');

        Route::get('/validation/agreement/{agreement_id}/{af_id}', 'CommerceController@validationAgreement');
        Route::post('/validationform/agreement/{agreement_id}/{af_id}', 'CommerceController@validationformAgreement');

        Route::get('/validation/invoice/{invoice_id}/{af_id}', 'CommerceController@validationInvoice');
        Route::post('/validationform/invoice/{invoice_id}/{af_id}', 'CommerceController@validationformInvoice');

        Route::post('/upload/contract/attached/documents', 'CommerceController@uploadContractAttachedDocuments');

        Route::post('/contact', 'ClientController@storeFormContact');
        Route::get('/adresse/{adresse_id}/{entitie_id}', 'ClientController@formAdresse');
        Route::post('/adresse', 'ClientController@storeFormAdresse');
        Route::get('/user/{user_id}', 'UserController@formUser');
        Route::post('/user', 'UserController@storeFormUser');
        Route::get('/param/{param_id}', 'AdminController@formParam');
        Route::post('/param', 'AdminController@storeFormParam');

        Route::get('/helpindex/{id}', 'AdminController@formHelpindex');
        Route::post('/helpindex', 'AdminController@storeFormHelpindex');

        Route::get('/af/{af_id}', 'ActionFormationController@formAf');
        Route::post('/af', 'ActionFormationController@storeFormAf');
        Route::get('/ptemplate/{ptemplate_id}', 'AdminController@formPtemplate');
        Route::post('/ptemplate', 'AdminController@storeFormPtemplate');

        Route::get('/session/{session_id}/{af_id}', 'ActionFormationController@formSession');
        Route::get('/certSession/{af_id}', 'ActionFormationController@formCertificationSession');
        Route::post('/saveCertSession/{af_id}', 'ActionFormationController@formSaveCertificationSession');
        Route::get('/session/test/{session_id}/{af_id}', 'ActionFormationController@formSessionTest');
        Route::get('/group/{group_id}/{af_id}', 'ClientController@formGroup');
        Route::get('/affectation/group/{group_id}/{af_id}', 'ClientController@formAffectationGroup');
        Route::post('/affectation/group', 'ClientController@storeFormAffectationGroup');
        Route::post('/session', 'ActionFormationController@storeFormSession');
        Route::post('/group', 'ClientController@storeFormGroup');
        //Stage
        Route::get('/stage/{session_id}/{af_id}', 'StageController@formStage');
        Route::post('/stage', 'StageController@storeFormStage');
        Route::get('/presence/{schedulecontact_id}', 'PresencesController@formPresence');
        Route::get('/presence/attachments/{schedule_id}/{membre_id}', 'PresencesController@formPresenceAttachments');

        Route::get('/stage/proposal/{internshiproposal_id}/{af_id}', 'StageController@formStageProposal');
        Route::get('/stage/proposal/attachments/{internshiproposal_id}/{af_id}', 'StageController@formStageProposalAttachements');
        Route::post('/stage/proposal/attachments/upload/{internshiproposal_id}', 'StageController@formStageProposalAttachementsUpload');
        Route::get('/stage/proposal/attachments/getmedias/{internshiproposal_id}', 'StageController@formStageProposalAttachementsGet');
        Route::post('/stage/proposal/attachments/delete/{media_id}', 'StageController@formStageProposalAttachementsDelete');
        Route::post('/stage/proposal', 'StageController@storeFormStageProposal');

        Route::get('/sessiondate/{session_id}/{sessiondate_id}', 'ActionFormationController@formSessionDate');
        Route::post('/sessiondate', 'ActionFormationController@storeFormSessionDate');
        Route::get('/enrollment/{af_id}/{enrollment_id}/{type}', 'ActionFormationController@formEnrollment');
        Route::post('/enrollment', 'ActionFormationController@storeFormEnrollment');
        //Route::get('/schedulecontact/{af_id}/{type}', 'ActionFormationController@formScheduleContact');
        Route::post('/schedulecontact', 'ActionFormationController@storeFormScheduleContact');
        Route::get('/price/{price_id}', 'AdminController@formPrice');
        Route::get('/price/type/{is_former}/{price_id}', 'AdminController@formPriceByType');
        Route::post('/price', 'AdminController@storeFormPrice');
        Route::get('/price/rel/pf/{pf_id}', 'FormationController@formRelPfPrice');
        Route::post('/price/rel/pf', 'FormationController@storeFormRelPfPrice');
        Route::get('/price/rel/af/{af_id}', 'ActionFormationController@formRelAfPrice');
        Route::post('/price/rel/af', 'ActionFormationController@storeFormRelAfPrice');
        Route::get('/enrollmentintervenants/{af_id}/{enrollment_id}', 'ActionFormationController@formEnrollmentIntervenants');
        Route::post('/enrollment/intervenants', 'ActionFormationController@storeFormEnrollmentIntervenants');
        //Route::get('/remuneration/{enrollment_id}', 'ActionFormationController@formRemuneration');
        Route::get('/remuneration/{af_id}/{member_id}', 'ActionFormationController@formRemuneration');
        Route::post('/remuneration', 'ActionFormationController@storeFormRemuneration');
        Route::get('/ressource/{param_id}', 'AdminController@formRessource');
        Route::post('/ressource', 'AdminController@storeFormRessource');
        Route::post('/scheduleressource', 'ActionFormationController@storeFormScheduleRessource');
        Route::get('/formerpricebytypeintervention/{member_id}', 'ActionFormationController@formFormerPriceByTypeIntervention');
        Route::get('/contract/{contract_id}/{af_id}', 'ActionFormationController@formContract');

        Route::get('/selectionafintervenant/{member_id}', 'ActionFormationController@formselectionafintervenant');
        Route::post('/envoyerdemande/{id_schedule}/{member_id}', 'ActionFormationController@formsendrequesttask');
        Route::post('/envoyerdemandealot/{member_id}', 'ActionFormationController@envoyerdemandealot');
        Route::get('/attached/documents/estimatesfact/{estimate_id}/{af_id}', 'CommerceController@getAttachedDocumentEstimatesFact');

        Route::post('/contract', 'ActionFormationController@storeFormContract');
        Route::get('/af/sheet/{af_id}/{id}', 'ActionFormationController@formAfSheet');
        Route::post('/af/sheet', 'ActionFormationController@storeFormAfSheet');
        Route::get('/document/new', 'AdminController@formNewDocument');
        Route::get('/email/new', 'TaskController@formNewEmail');
        Route::get('/document/{document_model_id}', 'AdminController@formDocument');
        Route::get('/email/getviewvars', 'TaskController@getViewVars');
        Route::get('/email/{email_model_id}', 'TaskController@formEmail');
        Route::get('/sentaskemail/{email_model_id}', 'TaskController@sendTaskFormEmail');
        Route::post('/document', 'AdminController@storeFormDocument');
        Route::post('/email', 'TaskController@storeFormEmail');
        Route::get('/estimate/{estimate_id}/{af_id}/{entity_id}', 'CommerceController@formEstimate');
        Route::post('/estimate', 'CommerceController@storeFormEstimate');
        Route::get('/estimate-item/{item_id}/{estimate_id}', 'CommerceController@formEstimateItem');
        Route::post('/estimate-item', 'CommerceController@storeFormEstimateItem');
        Route::get('/discount/{estimate_id}', 'CommerceController@formDiscount');
        Route::post('/discount', 'CommerceController@storeFormDiscount');
        Route::get('/agreement/{agreement_id}/{af_id}/{entity_id}', 'CommerceController@formAgreement');
        Route::post('/agreement', 'CommerceController@storeFormAgreement');
        Route::get('/agreement-item/{item_id}/{agreement_id}', 'CommerceController@formAgreementItem');
        Route::post('/agreement-item', 'CommerceController@storeFormAgreementItem');
        Route::get('/agreement/discount/{agreement_id}', 'CommerceController@formAgreementDiscount');
        Route::post('/agreement/discount', 'CommerceController@storeFormAgreementDiscount');
        Route::get('/funding/{funding_id}/{agreement_id}', 'CommerceController@formFunding');
        Route::post('/funding', 'CommerceController@storeFormFunding');
        Route::get('/fundingpayment/{fundingpayment_id}/{funding_id}', 'CommerceController@formFundingPayment');
        Route::post('/fundingpayment', 'CommerceController@storeFormFundingPayment');
        Route::get('/invoice/{invoice_id}/{af_id}', 'CommerceController@formInvoice');
        Route::post('/invoice', 'CommerceController@storeFormInvoice');
        Route::post('/convocation', 'CommerceController@generateConvocation');
        Route::get('/convocation/{convocation_id}/{af_id}', 'CommerceController@formConvocation');
        Route::get('/payment/{payment_id}/{invoice_id}', 'CommerceController@formPayment');
        Route::post('/payment', 'CommerceController@storeFormPayment');
        Route::get('/pointage/{schedulecontact_id}', 'ActionFormationController@formPointage');
        Route::get('/score/{schedulecontact_id}', 'ActionFormationController@formScore');
        Route::get('/scores/{block_id}', 'ActionFormationController@formScores');
        Route::post('/scores', 'ActionFormationController@storeFormScores');
        Route::get('/committee/{member_id}/{id}', 'ActionFormationController@formCommittee');
        Route::post('/committee', 'ActionFormationController@storeFormCommittee');
        Route::post('/pointage', 'ActionFormationController@storeFormPointage');
        Route::post('/pointage/bulk', 'ActionFormationController@storeFormBulkPointage');
        Route::prefix('mail')->group(function () {
            Route::get('/invoice/{invoice_id}', 'CommerceController@formMailInvoice');
            Route::post('/invoice', 'CommerceController@storeFormMailInvoice');
            Route::get('/agreement/{agreement_id}', 'CommerceController@formMailAgreement');
            Route::post('/devis/{id_schedule}/{member_id}', 'ActionFormationController@formMailDevis');
            Route::post('/agreement', 'CommerceController@storeFormMailAgreement');
            Route::get('/estimate/{estimate_id}', 'CommerceController@formMailEstimate');
            Route::post('/estimate', 'CommerceController@storeFormMailEstimate');
            Route::post('/task', 'TaskController@sendEmail');
        });
        Route::get('/invoice-from-agreement/{agreement_id}', 'CommerceController@formInvoiceFromAgreement');
        Route::post('/invoice-from-agreement', 'CommerceController@storeFormInvoiceFromAgreement');
        Route::get('/groupment/{groupment_id}/{af_id}', 'ClientController@formGroupment');
        Route::post('/groupment', 'ClientController@storeFormGroupment');
        Route::get('/affectation/groupment/{groupment_id}/{af_id}', 'ClientController@formAffectationGroupment');
        Route::post('/affectation/groupment', 'ClientController@storeFormAffectationGroupment');
        Route::get('/unknown/contact/{member_id}/{enrollment_id}', 'ClientController@formUnknownContact');
        Route::post('/unknown/contact', 'ClientController@storeFormUnknownContact');
        //students invoices
        Route::get('/students/invoices/{af_id}', 'CommerceController@formStudentsInvoices');
        Route::post('/students/invoices', 'CommerceController@storeFormStudentsInvoices');
        //Preplanifications
        Route::get('/preplanification/{planification_id}', 'CalendarController@formPreplanifications');
        Route::get('/select/formation', 'CalendarController@selectFormation');
        Route::get('/select/af', 'CalendarController@AFcible');
        Route::get('/select/grp/{af_id}', 'CalendarController@GroupeSelect');
        Route::post('/update/grp/{pp_schedule_id}', 'CalendarController@updateGroupeSelect');
        Route::get('/select/Regroupement/{af_id}', 'CalendarController@RegroupementSelect');
        Route::post('/update/Regroupement/{pp_schedule_id}', 'CalendarController@updateRegroupementSelect');
        Route::post('/update/interv/{pp_schedule_id}', 'CalendarController@updateIntervenant');
        Route::patch('/edit/interv/{id}', 'CalendarController@editIntervenant');
        Route::delete('/delete/interv/{id}', 'CalendarController@deleteIntervenant');
        Route::get('/loadmodal/{pp_schedule_id}', 'CalendarController@loadModal');
        Route::get('/select/intervenants', 'CalendarController@SelectListIntervenant');
        Route::get('/select/intervenants_price', 'CalendarController@SelectListIntervenantPrice');
        Route::get('/show/show-intervenants/{pp_schedule_id}', 'CalendarController@ShowListIntervenant');
        Route::post('/Preplanifications/{produitFormation}', 'CalendarController@storeFormPreplanifications');
        Route::post('/Preplanifications/update/{planification_id}', 'CalendarController@updatePreplanification');
        Route::post('/transfererPplanifications/', 'CalendarController@transfererPplanifications')->name('transPplanifications');
        Route::post('/transfer/preplanning', 'CalendarController@transferPreplanning')->name('transfer.preplanning');

        //pregenerate PNM
        Route::get('/downloadsagefile', 'CommerceController@downloadSageFile');
        //generate PNM
        Route::get('/generate/pnm', 'CommerceController@generatePnm');
        //generate PNM Avoirs
        Route::get('/generate/avoirs/pnm', 'CommerceController@generatePnmAvoirs');
        //generate PNC
        Route::get('/generate/pnc', 'CommerceController@generatePnc');
        //invoice item
        Route::get('/invoice-item/{item_id}/{invoice_id}', 'CommerceController@formInvoiceItem');
        Route::post('/invoice-item', 'CommerceController@storeFormInvoiceItem');
        Route::get('/param-to-item/{i}', 'CommerceController@formParamToItem');

        Route::get('/refund/{refund_id}/{invoice_id}', 'CommerceController@formRefund');
        Route::post('/refund', 'CommerceController@storeFormRefund');
    });

    Route::prefix('get')->group(function () {
        Route::get('/params/{param_code}', 'FormationController@getParamsByParamCode');
        Route::get('/categories/{id}', 'FormationController@getJsonCategories');
        Route::get('/session/dates/{session_id}', 'ActionFormationController@getSessionDates');
        Route::get('/session/gridlist/{af_id}', 'ActionFormationController@getSessionsGridList');
        Route::get('/session/summary/dates/{session_id}', 'ActionFormationController@getSessionSummaryDates');
        Route::get('/members/{enrollment_id}', 'ActionFormationController@getMembers');
        Route::get('/subtasks/{taskid}', 'TaskController@getSubTasks');
        Route::get('/entitie/{entity_id}', 'ClientController@getJsonEntitie');
        Route::get('/infos/pf/{formation_id}', 'FormationController@getInfosPFormation');
        Route::get('/estimate/items/{estimate_id}', 'CommerceController@getEstimateItems');
        Route::get('/agreement/items/{agreement_id}', 'CommerceController@getAgreementItems');
        Route::get('/agreement/fundings/{agreement_id}', 'CommerceController@getFundings');

        Route::get('/invoice/items/{invoice_id}', 'CommerceController@getInvoiceItems');
        Route::get('/fundingpayment/amount/{fundingpayment_id}', 'CommerceController@getAmountFundingPayment');
        Route::get('/session/infos/{session_id}', 'ActionFormationController@getSessionInfos');
        //structure hiérarchique
        Route::get('/hierarchical/structure/{pf_id}', 'FormationController@getHierarchicalStructure');
        Route::get('/tree/hierarchical/structure/{pf_id}', 'FormationController@getJsonTreeProductHierarchicalStructure');
        Route::get('/tree/time/structure/{pf_id}/{param}', 'FormationController@getJsonTimeStructure');
        Route::get('/structure/{pf_id}', 'CalendarController@getJsonTimeStructure');
        Route::get('/tree/timeaf/structure/{af_id}/{param}/{is_eval?}', 'ActionFormationController@getJsonTimeStructure');
        Route::get('/tree/timeSession/structure/{af_id}/{block_id?}', 'ActionFormationController@getJsonTimeSessionStructure');
        Route::get('/tree/timeMember/structure/{af_id}', 'ActionFormationController@getJsonTimeMembersStructure');
        Route::get('/parent/product/view/{pf_id}', 'FormationController@getParentProductView');//view select
        Route::get('/schedule/member/details/{af_id}/{member_id}', 'ActionFormationController@getScheduleMemberDetails');
        Route::get('/schedule/contract/details/{contract_id}', 'ActionFormationController@getScheduleContractDetails');
        Route::get('/schedule/contract/details/periods/{contract_id}/{start}/{end}', 'ActionFormationController@getScheduleContractDetailsByPeriods');
        Route::get('/content/tab/docs/{block_id}/{af_id}', 'ActionFormationController@getContentTabDocsAf');
        Route::get('/content/tab/certs/{block_id}/{af_id}', 'ActionFormationController@getContentTabCertsAf');
        Route::get('/block/funders', function () {
            return view('pages.commerce.invoice.form.funders-select');
        });
        Route::get('/attached/documents/{af_id}/{certificate_id}', 'CommerceController@getAttachedDocuments');
        Route::get('/attached/documents/contract/{af_id}/{contract_id}', 'CommerceController@getAttachedDocumentsContract');
    });
    Route::post('/session/gridlist/search/{af_id}', 'ActionFormationController@searchSessionGridList');


    Route::prefix('copy')->group(function () {
        Route::get('/price/pf/af/{af_id}', 'ActionFormationController@copyPricesFromPfToAf');
        Route::get('/sheet/pf/af/{af_id}', 'ActionFormationController@copySheetFromPfToAf');
    });
    Route::prefix('admin')->group(function () {
        Route::get('/catalogues', 'CatalogueController@list');
        Route::get('/structure_temps', 'AdminController@structureTemps');
        Route::get('/logs', 'AdminController@logs');
        Route::get('/parametrages', 'AdminController@params');
        Route::get('/ptemplates', 'AdminController@ptemplates');
        Route::get('/prices', 'AdminController@prices');
        Route::get('/ressources', 'AdminController@ressources');
        Route::get('/documents', 'AdminController@documents');
        Route::get('/emails', 'AdminController@emails');
        Route::get('/indexes', 'AdminController@indexes');
    });


Route::get('/technical-doc', 'DocumentationController@index');

/* Route::get('/datatables', 'PagesController@datatables');
Route::get('/ktdatatables', 'PagesController@ktDatatables');
Route::get('/select2', 'PagesController@select2');
Route::get('/jquerymask', 'PagesController@jQueryMask');
Route::get('/icons/custom-icons', 'PagesController@customIcons');
Route::get('/icons/flaticon', 'PagesController@flaticon');
Route::get('/icons/fontawesome', 'PagesController@fontawesome');
Route::get('/icons/lineawesome', 'PagesController@lineawesome');
Route::get('/icons/socicons', 'PagesController@socicons');
Route::get('/icons/svg', 'PagesController@svg'); */

Route::get('/quick-search', 'PagesController@quickSearch')->name('quick-search');
Route::get('/dailyschedule', 'PagesController@dailyschedule')->name('dailyschedule');
Route::get('/dailyschedule/json', 'PagesController@dailyscheduleJson')->name('dailyscheduleJson');
Route::get('/wordfilegenerate', 'WordController@generate');

/* API */
Route::get('/ged/closetask', 'SignApiController@closeTask');
Route::post('/ged/closetask', 'SignApiController@closeTask');
Route::post('/test/one','FormationController@createDepotDevis')->name('depot_devis');

require __DIR__ . '/auth.php';
