<?php
/**
 * Raised when authorization fails
 * Raised when the API key being used is not authorized to perform
 * the attempted action according to the scopes assigned to the app
 * who owns the API key.
 *
 */
class Tiendy_Exception_Authorization extends Exception
{
}