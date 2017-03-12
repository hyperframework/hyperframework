<?php
namespace Hyperframework\Common;

class Inflector {
    /**
     * @param string $word
     * @return string
     */
    public static function pluralize($word) {
        return self::convert($word, true);
    }

    /**
     * @param string $word
     * @return string
     */
    public static function singularize($word) {
        return self::convert($word, false);
    }

    /**
     * @param string $word
     * @param bool $isSingular
     * @return string
     */
    private static function convert($word, $isSingular) {
        $word = (string)$word;
        if ($word === '') {
            return '';
        }
        $originalWord = $word;
        $word = strtolower($word);
        static $specialWords = [
            'atlas' => 'atlases',
            'cafe' => 'cafes',
            'carp' => 'carp',
            'chassis' => 'chassis',
            'child' => 'children',
            'clippers' => 'clippers',
            'cod' => 'cod',
            'cookie' => 'cookies',
            'corpus' => 'corpuses',
            'curve' => 'curves',
            'equipment' => 'equipment',
            'fish' => 'fish',
            'foe' => 'foes',
            'genie' => 'genies',
            'genus' => 'genera',
            'graffito' => 'graffiti',
            'information' => 'information',
            'jeans' => 'jeans',
            'loaf' => 'loaves',
            'louse' => 'lice',
            'man' => 'men',
            'money' => 'money',
            'mouse' => 'mice',
            'move' => 'moves',
            'news' => 'news',
            'nexus' => 'nexus',
            'niche' => 'niches',
            'opus' => 'opuses',
            'ox' => 'oxen',
            'person' => 'people',
            'pincers' => 'pincers',
            'pliers' => 'pliers',
            'police' => 'police',
            'rice' => 'rice',
            'salmon' => 'salmon',
            'scissors' => 'scissors',
            'shears' => 'shears',
            'sheep' => 'sheep',
            'species' => 'species',
            'turf' => 'turfs',
            'wave' => 'waves',
            'woman' => 'women',
            'zombie' => 'zombies'
        ];
        $result = false;
        if ($isSingular) {
            if (isset($specialWords[$word])) {
                $result = $specialWords[$word];
            }
        } else {
            $result = array_search($word, $specialWords, true);
        }
        if ($result === false) {
            if ($isSingular) {
                static $pluralRules = [
                    '/(quiz)$/' => '\1zes',
                    '/(matr|vert|ind)(?:ix|ex)$/' => '\1ices',
                    '/(x|ch|ss|sh)$/' => '\1es',
                    '/([^aeiouy]|qu)y$/' => '\1ies',
                    '/(hive)$/' => '\1s',
                    '/(?:([^f])fe|([lr])f)$/' => '\1\2ves',
                    '/sis$/' => 'ses',
                    '/([ti])a$/' => '\1a',
                    '/([ti])um$/' => '\1a',
                    '/(buffal|tomat)o$/' => '\1oes',
                    '/(bu)s$/' => '\1ses',
                    '/(alias|status)$/' => '\1es',
                    '/(octop|vir)i$/' => '\1i',
                    '/(octop|vir)us$/' => '\1i',
                    '/^(ax|test)is$/' => '\1es',
                    '/s$/' => 's',
                    '/$/' => 's'
                ];
                $rules =& $pluralRules;
            } else {
                static $singularRules = [
                    '/(database)s$/' => '\1',
                    '/(quiz)zes$/' => '\1',
                    '/(matr)ices$/' => '\1ix',
                    '/(vert|ind)ices$/' => '\1ex',
                    '/(alias|status)(es)?$/' => '\1',
                    '/(octop|vir)(us|i)$/' => '\1us',
                    '/^(a)x[ie]s$/' => '\1xis',
                    '/(cris|test)(is|es)$/' => '\1is',
                    '/(shoe)s$/' => '\1',
                    '/(o)es$/' => '\1',
                    '/(bus)(es)?$/' => '\1',
                    '/(x|ch|ss|sh)es$/' => '\1',
                    '/(m)ovies$/' => '\1ovie',
                    '/(s)eries$/' => '\1eries',
                    '/([^aeiouy]|qu)ies$/' => '\1y',
                    '/([lr])ves$/' => '\1f',
                    '/(tive)s$/' => '\1',
                    '/(hive)s$/' => '\1',
                    '/([^f])ves$/' => '\1fe',
                    '/(^analy)(sis|ses)$/' => '\1sis',
                    '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)(sis|ses)$/'
                        => '\1sis',
                    '/([ti])a$/' => '\1um',
                    '/(ss)$/' => '\1',
                    '/s$/' => ''
                ];
                $rules =& $singularRules;
            }
            foreach ($rules as $rule => $replacement) {
                if (preg_match($rule, $word)) {
                    $result = preg_replace($rule, $replacement, $word);
                    break;
                }
            }
        }
        if ($result === false || $result === $originalWord) {
            return $originalWord;
        } else {
            if (ctype_upper($originalWord)) {
                return strtoupper($result);
            }
            if (ctype_upper($originalWord[0])) {
                return ucfirst($result);
            }
            return $result;
        }
    }
}
