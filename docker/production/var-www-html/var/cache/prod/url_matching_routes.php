<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/logout' => [
            [['_route' => '_logout_main'], null, null, null, false, false, null],
            [['_route' => 'app_security_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/achievements/leaderboard' => [[['_route' => 'app_achievement_getleaderboard', '_controller' => 'App\\Controller\\AchievementController::getLeaderboard'], null, ['GET' => 0], null, false, false, null]],
        '/api/achievements/mine' => [[['_route' => 'app_achievement_getmyachievements', '_controller' => 'App\\Controller\\AchievementController::getMyAchievements'], null, ['GET' => 0], null, false, false, null]],
        '/api/dashboard' => [[['_route' => 'app_dashboard_getdashboard', '_controller' => 'App\\Controller\\DashboardController::getDashboard'], null, ['GET' => 0], null, false, false, null]],
        '/api/dev/mercure/ping' => [[['_route' => 'app_devmercure_ping', '_controller' => 'App\\Controller\\DevMercureController::ping'], null, ['POST' => 0], null, false, false, null]],
        '/api/notes' => [
            [['_route' => 'app_notes_getnotes', '_controller' => 'App\\Controller\\NotesController::getNotes'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_notes_createnote', '_controller' => 'App\\Controller\\NotesController::createNote'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/reports' => [[['_route' => 'app_report_getreports', '_controller' => 'App\\Controller\\ReportController::getReports'], null, ['GET' => 0], null, false, false, null]],
        '/api/register' => [[['_route' => 'app_security_register', '_controller' => 'App\\Controller\\SecurityController::register'], null, ['POST' => 0], null, false, false, null]],
        '/api/user' => [[['_route' => 'app_security_getcurrentuser', '_controller' => 'App\\Controller\\SecurityController::getCurrentUser'], null, ['GET' => 0], null, false, false, null]],
        '/api/user/profile' => [[['_route' => 'app_security_updateprofile', '_controller' => 'App\\Controller\\SecurityController::updateProfile'], null, ['PATCH' => 0], null, false, false, null]],
        '/api/user/profile-image' => [
            [['_route' => 'app_security_updateprofileimage', '_controller' => 'App\\Controller\\SecurityController::updateProfileImage'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'app_security_deleteprofileimage', '_controller' => 'App\\Controller\\SecurityController::deleteProfileImage'], null, ['DELETE' => 0], null, false, false, null],
        ],
        '/api/user/password' => [[['_route' => 'app_security_updatepassword', '_controller' => 'App\\Controller\\SecurityController::updatePassword'], null, ['PATCH' => 0], null, false, false, null]],
        '/api/login' => [[['_route' => 'app_security_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, ['POST' => 0], null, false, false, null]],
        '/api/subjects' => [[['_route' => 'app_subjects_getsubjects', '_controller' => 'App\\Controller\\SubjectsController::getSubjects'], null, ['GET' => 0], null, false, false, null]],
        '/api/tasks' => [
            [['_route' => 'app_tasks_gettasks', '_controller' => 'App\\Controller\\TasksController::getTasks'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_tasks_createtask', '_controller' => 'App\\Controller\\TasksController::createTask'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/live-timer-sessions' => [[['_route' => 'app_livetimersession_session_create', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionController::create'], null, ['POST' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/api/(?'
                    .'|notes/([^/]++)(?'
                        .'|(*:32)'
                    .')'
                    .'|subjects/([^/]++)(*:57)'
                    .'|tasks/([^/]++)(?'
                        .'|/toggle(*:88)'
                        .'|(*:95)'
                    .')'
                    .'|live\\-timer\\-sessions/([^/]++)(?'
                        .'|/(?'
                            .'|s(?'
                                .'|t(?'
                                    .'|art(*:151)'
                                    .'|op\\-and\\-save(*:172)'
                                .')'
                                .'|kip\\-phase(*:191)'
                            .')'
                            .'|pause(*:205)'
                            .'|res(?'
                                .'|ume(*:222)'
                                .'|et\\-phase(*:239)'
                            .')'
                            .'|change\\-config(*:262)'
                        .')'
                        .'|(*:271)'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        32 => [
            [['_route' => 'app_notes_deletenote', '_controller' => 'App\\Controller\\NotesController::deleteNote'], ['id'], ['DELETE' => 0], null, false, true, null],
            [['_route' => 'app_notes_updatenote', '_controller' => 'App\\Controller\\NotesController::updateNote'], ['id'], ['PUT' => 0], null, false, true, null],
        ],
        57 => [[['_route' => 'app_subjects_deletesubject', '_controller' => 'App\\Controller\\SubjectsController::deleteSubject'], ['id'], ['DELETE' => 0], null, false, true, null]],
        88 => [[['_route' => 'app_tasks_toggletask', '_controller' => 'App\\Controller\\TasksController::toggleTask'], ['id'], ['PUT' => 0], null, false, false, null]],
        95 => [[['_route' => 'app_tasks_deletetask', '_controller' => 'App\\Controller\\TasksController::deleteTask'], ['id'], ['DELETE' => 0], null, false, true, null]],
        151 => [[['_route' => 'app_livetimersession_sessionaction_start', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::start'], ['id'], ['POST' => 0], null, false, false, null]],
        172 => [[['_route' => 'app_livetimersession_sessionaction_stopandsave', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::stopAndSave'], ['id'], ['POST' => 0], null, false, false, null]],
        191 => [[['_route' => 'app_livetimersession_sessionaction_skipphase', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::skipPhase'], ['id'], ['POST' => 0], null, false, false, null]],
        205 => [[['_route' => 'app_livetimersession_sessionaction_pause', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::pause'], ['id'], ['POST' => 0], null, false, false, null]],
        222 => [[['_route' => 'app_livetimersession_sessionaction_resume', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::resume'], ['id'], ['POST' => 0], null, false, false, null]],
        239 => [[['_route' => 'app_livetimersession_sessionaction_resetphase', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::resetPhase'], ['id'], ['POST' => 0], null, false, false, null]],
        262 => [[['_route' => 'app_livetimersession_sessionaction_changeconfig', '_format' => 'json', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionActionController::changeConfig'], ['id'], ['POST' => 0], null, false, false, null]],
        271 => [
            [['_route' => 'app_livetimersession_session_getone', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionController::getOne'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'app_livetimersession_session_delete', '_controller' => 'App\\LiveTimerSession\\Controller\\SessionController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
