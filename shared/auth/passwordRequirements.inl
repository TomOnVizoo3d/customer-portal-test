CHECK_REGEX(passwordRules, "^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?(?:[^\\w]|_)).{8,}$")

#undef CHECK_REGEX
