<?php

/*
 * This file is part of fof/secure-https.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\SecureHttps;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;

class ModifyContentHtml
{
    protected $regex = '/<img src="http:\/\/([^"]+?)" (?:title="([^"]*?)" )?alt="([^"]*?)">/i';

    protected $subst = '<img onerror="$(this).next().empty().append(\'<blockquote style=&#92;&#39;background-color: #c0392b; color: white;&#92;&#39; class=&#92;&#39;uncited&#92;&#39;><div><p>\'+app.translator.trans(\'fof-secure-https.forum.removed\')+\' | <a href=&#92;&#39;http://$1&#92;&#39; style=&#92;&#39;color:white;&#92;&#39;target=&#92;&#39;_blank&#92;&#39;>\'+app.translator.trans(\'fof-secure-https.forum.show\')+\'</a></p></div></blockquote>\');$(this).hide();" onload="$(this).next().empty();" class="securehttps-replaced" src="https://$1" title="$2" alt="$3"><span><i class="icon fa fa-spinner fa-spin"></i> &nbsp;Loading Image</span>';

    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(Schema\Str $field): Schema\Str
    {
        // Keep serializers other extensions have set on this field.
        $previous = (fn () => $this->serializer)->call($field);

        return $field->serialize(function (mixed $value, Context $context) use ($previous) {
            if ($previous) {
                $value = $previous($value, $context);
            }

            if (!(bool) $this->settings->get('fof-secure-https.proxy') && is_string($value)) {
                $value = preg_replace($this->regex, $this->subst, $value);
            }

            return $value;
        });
    }
}
