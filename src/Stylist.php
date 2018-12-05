<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage;

/**
 * Klasa abstrakcyjna stylisty
 *
 * Stylista to obiekt, ktory przetwarza obraz wedlug okreslonego schematu
 * (np. przycina, wyszarza, zmniejsza itp.)
 *
 * Kazdy stylista powinien miec metode stylize($image), ktora pobiera oryginal
 * w formie ciagu bajtow i zwraca go po przetworzeniu, tez w formie ciagu bajtow
 *
 * Podklasy stylistow powinny znajdowac sie w folderze stylists
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Stylist
{

    /**
     * Pobiera oryginal i zwraca w przetworzonej formie. Dane wejsciowe to reource obrazu dla biblioteki
     * PHP GD, a wyjscie to resource z przetworzonym obrazem
     *
     * @param $originStream resource
     * @param $extension    string
     * @param $stylistObj   object
     * @param $stylistParam array
     */
    abstract public function stylize($originStream, $extension, $stylistObj, $stylistParam);

    /**
     * Zwraca unikalna nazwe stylisty, takze w zaleznosci od parametrow
     *
     * @param  array
     *
     * @return string
     */
    abstract public function identify($stylistParam);
}
