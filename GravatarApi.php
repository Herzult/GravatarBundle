<?php

namespace Bundle\GravatarBundle;

/**
 * Simple wrapper to the gravatar API
 * http://en.gravatar.com/site/implement/url
 *
 * Usage:
 *      \Bundle\GravatarBundle\GravatarApi::getUrl('henrik@bearwoods.dk', 80, 'g', 'mm');
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author     Henrik Bjørnskov <henrik@bearwoods.dk>
 */
class GravatarApi
{
    /**
     * @var array $default array of default options that can be overriden with getters and in the construct.
     */
    protected $defaults = array(
        'size'    => 80,
        'rating'  => 'g',
        'default' => null,
    );

    /**
     * Constructor
     *
     * @param array $options the array is merged with the defaults.
     * @return void
     */
    public function __construct(array $options = array())
    {
        $this->defaults = array_merge($this->defaults, $options);
    }

    /**
     * Returns a url for a gravatar.
     *
     * @param  string  $email
     * @param  integer $size
     * @param  string  $rating
     * @param  string  $default
     * @return string
     */
    public function getUrl($email, $size = null, $rating = null, $default = null)
    {
        $hash = md5(strtolower($email));

        $map = array(
            's' => $size    ?: $this->defaults['size'],
            'r' => $rating  ?: $this->defaults['rating'],
            'd' => $default ?: $this->defaults['default'],
        );

        return 'http://www.gravatar.com/avatar/' . $hash . '?' . http_build_query(array_filter($map));
    }

    /**
     * Checks if a gravatar exists for the email. It does this by checking for 404 Not Found in the
     * body returned.
     *
     * @param string $email
     * @return boolean
     */
    public function exists($email)
    {
        $path = $this->getUrl($email, null, null, '404');

        $sock = fsockopen('gravatar.com', 80, $errorNo, $error);
        fputs($sock, "HEAD " . $path . " HTTP/1.0\r\n\r\n");

        $header = fgets($sock, 128);

        fclose($sock);

        return trim($header) == 'HTTP/1.1 404 Not Found' ? false : true;
    }
}
