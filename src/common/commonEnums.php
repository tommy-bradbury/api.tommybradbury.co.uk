<?php

enum UserSearchableFields {
    case EMAIL;
    case ID;
}

enum LocationSearchableFields {
    case ID;
    case USER_ID;
}

enum ReviewSearchableFields {
    case ID;
    case LOCATION_ID;
    case USER_ID;
}