UPDATE `engine4_user_signup` SET class = REPLACE(`class`,"Sessociallogin_","User_");

INSERT INTO engine4_user_signup (`class`,`order`,`enable`) SELECT REPLACE(`class`,"User_","Sessociallogin_"),`order`,1 FROM engine4_user_signup WHERE class IN ("User_Plugin_Signup_Account","User_Plugin_Signup_Fields","User_Plugin_Signup_Photo");

UPDATE `engine4_user_signup` SET enable = 0 WHERE class IN ("User_Plugin_Signup_Account","User_Plugin_Signup_Fields","User_Plugin_Signup_Photo");
