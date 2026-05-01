<?php
// namespace LdGroupRegistration\Includes;

// Default settings for : When admin accepts the user removal request (Group Leader)
if (! defined('WDM_GR_GL_RMVL_SUB')) {
    define('WDM_GR_GL_RMVL_SUB', 'User Removal request from group');
}
if (! defined('WDM_GR_GL_RMVL_BODY')) {
    $message = <<<MESSAGE
    Hi,

    Your request for removing {user_email} from the {group_title} has been accepted by admin
MESSAGE;
    define('WDM_GR_GL_RMVL_BODY', $message);
}

// Default settings for : When admin rejects the user removal request (Group Leader)
if (! defined('WDM_GR_GL_ACPT_SUB')) {
    define('WDM_GR_GL_ACPT_SUB', 'User Removal request from group');
}
if (! defined('WDM_GR_GL_ACPT_BODY')) {
        $message = <<<MESSAGE
        Hi,

        Your request for removing {user_email} from the {group_title} has been rejected by admin
MESSAGE;
    define('WDM_GR_GL_ACPT_BODY', $message);
}

// Default settings for : When Group Leader requests to remove user (Admin)
if (! defined('WDM_A_RQ_RMVL_SUB')) {
    define('WDM_A_RQ_RMVL_SUB', 'User Removal request from group');
}
if (! defined('WDM_A_RQ_RMVL_BODY')) {
        $message = <<<MESSAGE
        Hi,

        <b>{group_leader_name}</b> has requested for removing <b>{user_email}</b> from <b>{group_title}</b>

        You can find removal request <a href = '{group_edit_link}'>here</a>
MESSAGE;
    define('WDM_A_RQ_RMVL_BODY', $message);
}

// Default settings for : When User account gets created and added into Group (User)
if (! defined('WDM_U_AC_CRT_SUB')) {
    define('WDM_U_AC_CRT_SUB', 'Your account is created on {site_name}');
}
if (! defined('WDM_U_AC_CRT_BODY')) {
        $message = <<<MESSAGE
        Hello {user_first_name} {user_last_name},

        Your account is created on our website, please find login details given below:

        Email: {user_email}
        Password: {user_password}

        Click <a href = '{login_url}'>here</a> to login & start learning.
MESSAGE;
    define('WDM_U_AC_CRT_BODY', $message);
}

// Default settings for : When User account gets created (Admin)
if (! defined('WDM_A_U_AC_CRT_SUB')) {
    define('WDM_A_U_AC_CRT_SUB', '[{site_name}] New User Registration');
}
if (! defined('WDM_A_U_AC_CRT_BODY')) {
        $message = <<<MESSAGE
        New user registration on your site {site_name}:

        Username: {user_email}
        E-mail: {user_login}
MESSAGE;
    define('WDM_A_U_AC_CRT_BODY', $message);
}

// Default settings for : When Group Leader sends ReInvite Email (User)
if (! defined('WDM_REINVITE_SUB')) {
    define('WDM_REINVITE_SUB', 'Your account is created on {site_name}');
}
if (! defined('WDM_REINVITE_BODY')) {
        $message = <<<MESSAGE
        Hi,

        Your account is created on our website. Please find your login details below:

        Email: {user_email}
        Password: <a href = '{reset_password}'>Reset Password</a>

        Click <a href = '{login_url}'>here</a> to login and to start the course.
MESSAGE;
    define('WDM_REINVITE_BODY', $message);
}

// Default settings for : When User gets added into Group (User)
if (! defined('WDM_U_ADD_GR_SUB')) {
    define('WDM_U_ADD_GR_SUB', 'Course Enrollment');
}
if (! defined('WDM_U_ADD_GR_BODY')) {
        $message = <<<MESSAGE
        Hello {user_first_name},

        Click <a href = '{login_url}'>here</a> to login & start learning.
MESSAGE;
    define('WDM_U_ADD_GR_BODY', $message);
}
