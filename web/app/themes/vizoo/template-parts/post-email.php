<?php

/**
 * E-Mail Generation
 * This file generates an HTML email based on a post on Wordpress.
 * It is called by webapps/customers/wp-content/mu-plugins/vizoo_customer_portal.php (section 15).
 *
 * $newsletter_content and $newsletter_url have to be defined:
 * - $newsletter_content: the filtered HTML string of the post's content
 * - $newsletter_url: the link provided to view the newsletter in browser).
 */

// if this file is accessed illegally (content and permalink aren't set), stop the execution
if (empty($newsletter_content) || empty($newsletter_url) || empty($newsletter_unsubscribe_url)) {
    wp_die();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Vizoo Newsletter</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            min-width: 100% !important;
        }

        img {
            height: auto;
        }

        .content {
            width: 100%;
            max-width: 600px;
        }

        .header {
            padding: 40px 30px 20px 30px;
        }

        .innerpadding {
            padding: 30px 30px 30px 30px;
        }

        .borderbottom {
            border-bottom: 1px solid #f2eeed;
        }

        .subhead {
            font-size: 15px;
            color: #ffffff;
            font-family: sans-serif;
            letter-spacing: 10px;
        }

        .h1,
        .h2,
        .bodycopy {
            color: #3b3b3b;
            font-family: sans-serif;
        }

        .h1 {
            font-size: 33px;
            line-height: 38px;
            font-weight: bold;
        }

        .h2 {
            padding: 0 0 15px 0;
            font-size: 24px;
            line-height: 28px;
            font-weight: bold;
        }

        .bodycopy {
            font-size: 16px;
            line-height: 22px;
            padding: 0 0 15px 0;
        }

        .button {
            text-align: center;
            font-size: 18px;
            font-family: sans-serif;
            font-weight: bold;
            padding: 0 30px 0 30px;
        }

        .button a {
            color: #ffffff;
            text-decoration: none;
        }

        .footer {
            padding: 20px 30px 15px 30px;
        }

        .footercopy {
            font-family: sans-serif;
            font-size: 14px;
            color: #3b3b3b;
        }

        .footercopy a {
            color: #3b3b3b;
            text-decoration: underline;
        }

        @media only screen and (max-width: 550px),
        screen and (max-device-width: 550px) {
            body[yahoo] .hide {
                display: none !important;
            }

            body[yahoo] .buttonwrapper {
                background-color: transparent !important;
            }

            body[yahoo] .button {
                padding: 0px !important;
            }

            body[yahoo] .button a {
                background-color: #e05443;
                padding: 15px 15px 13px !important;
            }

            body[yahoo] .unsubscribe {
                display: block;
                margin-top: 20px;
                padding: 10px 50px;
                background: #2f3942;
                border-radius: 5px;
                text-decoration: none !important;
                font-weight: bold;
            }
        }

        /*@media only screen and (min-device-width: 601px) {
    .content {width: 600px !important;}
    .col425 {width: 425px!important;}
    .col380 {width: 380px!important;}
    }*/
    </style>
</head>

<body yahoo bgcolor="#f9f9fa">
    <table width="100%" bgcolor="#f9f9fa" border="0" cellpadding="0" cellspacing="0" style="background-color: #f9f9fa;">
        <tr>
            <td>
                <table width="100%" bgcolor="#3b3b3b">
                    <tr>
                        <td></td>
                        <td>
                            <table cellpadding="14" style="cell-padding: 14px;">
                                <tr>
                                    <td>
                                        <img class="fix" src="https://customers.vizoo3d.com/wp-content/uploads/2017/12/vizoo-logo-mail.png" width="100" height="43" border="0" alt="Vizoo Logo" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <!--[if (gte mso 9)|(IE)]>
        <table width="600" bgcolor="#f9f9fa" align="center" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9fa;">
          <tr>
            <td>
      <![endif]-->
                <table bgcolor="#ffffff" class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff;">
                    <tr>
                        <td>
                            <table bgcolor="#f9f9fa" id="top-message" cellpadding="15" cellspacing="0" width="100%" align="center" style="text-align: center">
                                <tr>
                                    <td style="font-family: sans-serif; font-size: 12px">
                                        <a href="<?php echo $newsletter_url; ?>">View in your Browser</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php

                    /**
                     * First we have to reformat the HTML-string of the post's content.
                     *
                     * Since HTML email support is very basic, we have to convert the nice HTML-Markup into
                     * messy and old HTML. HTML5 tags have to be replaced, paragraph contents and lists need
                     * to be wrapped in divs as we parse the HTML later and use divs as section elements.
                     */

                    // strip all linebreaks to avoid messy formating
                    $newsletter_content = preg_replace('/\r|\n/', '', $newsletter_content);

// strip out unsupported html tags (blockquote, figure, picture and hr)
$newsletter_content = preg_replace('/\<\/?blockquote.*?\>/', '', $newsletter_content);
$newsletter_content = preg_replace('/\<\/?figure.*?\>/', '', $newsletter_content);
$newsletter_content = preg_replace('/\<\/?picture.*?\>/', '', $newsletter_content);
$newsletter_content = str_replace('<hr>', '', $newsletter_content);
$newsletter_content = str_replace('<hr/>', '', $newsletter_content);
$newsletter_content = str_replace('<hr />', '', $newsletter_content);

// wrap lists into divs, as we use divs as section elements later
$newsletter_content = str_replace('<ul', '<div><ul', $newsletter_content);
$newsletter_content = str_replace('</ul>', '</ul></div>', $newsletter_content);
$newsletter_content = str_replace('<ol', '<div><ol', $newsletter_content);
$newsletter_content = str_replace('</ol>', '</ol></div>', $newsletter_content);

// convert semantically correct HTML5 tags to deprecated style-based HTML tags
$newsletter_content = str_replace('<del>', '<span style="text-decoration: line-through;">', $newsletter_content);
$newsletter_content = str_replace('</del>', '</span>', $newsletter_content);
$newsletter_content = str_replace('<em', '<i', $newsletter_content);
$newsletter_content = str_replace('</em>', '</i>', $newsletter_content);
$newsletter_content = str_replace('<strong', '<b', $newsletter_content);
$newsletter_content = str_replace('</strong>', '</b>', $newsletter_content);

// convert paragraphs to divs, as we use divs as section elements later
$newsletter_content = str_replace('<p', '<div', $newsletter_content);
$newsletter_content = str_replace('</p>', '</div>', $newsletter_content);

// convert all headings to h2 to match design and reduce complexity
$newsletter_content = preg_replace('/\<h.( .*)?\>/', '<h2\1>', $newsletter_content);
$newsletter_content = preg_replace('/\<\/h.\>/', '</h2>', $newsletter_content);

// split the html string to sections at headings
$parts = preg_split('@(?=<h.*?\>)@', $newsletter_content);

foreach ($parts as $part) {
    // if the part is empty just ignore it
    if ($part != '') {
        ?>
                            <tr>
                                <td class="innerpadding borderbottom">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <?php

                            // now we parse the HTML string, therefore init a DOMDocument
                            $doc = new DOMDocument();

        // let us handle errors, so we can filter unsupported HTML5 elements
        libxml_use_internal_errors(true);

        // load in the (correctly encoded) HTML string (so we don't miss any umlauts)
        $doc->loadHTML(mb_convert_encoding($part, 'HTML-ENTITIES', 'UTF-8'));

        // fetch errors
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            // if the errors don't have anything to do with unsupported HTML5 elements (<video> and <source>), print out the error
            if (!$error->message == 'Tag video invalid' || !$error->message == 'Tag source invalid') {
                print_r($error);
            }
        }

        // reset the error handling
        libxml_use_internal_errors(false);

        // get the header of the section
        $header = $doc->getElementsByTagName('h2');

        // if there is one and it's no empty header..
        if ($header->length != 0) {
            $header_item = $header->item(0);

            if ($header_item->nodeValue != '') {
                // ..print out the table row
                $style = $header_item->getAttribute('style');
                if ($style == '') {
                    echo '<tr><td class="h2" style="padding: 0 0 15px 0; font-size: 24px; line-height: 28px; font-weight: bold;">' . $header_item->nodeValue . '</td></tr>';
                } else {
                    echo '<tr><td class="h2" style="padding: 0 0 15px 0; font-size: 24px; line-height: 28px; font-weight: bold; ' . $style . '">' . $header_item->nodeValue . '</td></tr>';
                }
            }
        }

        // get the paragraphs of the section
        $paragraphs = $doc->getElementsByTagName('div');
        $length = $paragraphs->length;
        for ($i = 0; $i < $length; $i++) {
            $paragraph = $paragraphs->item($i);

            // if the paragraph is no video..
            if ($paragraph->getAttribute('class') !== 'kgvid_wrapper') {

                // ..print out the table row
                $style = $paragraph->getAttribute('style');

                if ($style == '') {
                    echo '<tr><td class="bodycopy">' . getInnerHTML($paragraph) . '</td></tr>';
                } else {
                    echo '<tr><td class="bodycopy" style="' . $style . '">' . getInnerHTML($paragraph) . '</td></tr>';
                }
            }
        }

        ?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#f9f9fa" style="background-color: #f9f9fa; line-height: 2px; font-size: 2px;">&nbsp;</td>
                            </tr>
                    <?php
    }
}
?>
                    <tr>
                        <td class="footer">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="left" class="footercopy">
                                        <a href="<?php echo $newsletter_unsubscribe_url; ?>">Unsubscribe</a> | <a href="<?php echo $newsletter_url; ?>">View&nbsp;in&nbsp;your&nbsp;browser</a>
                                    </td>
                                    <td align="right" class="footercopy">
                                        Copyright &copy; <?php echo date("Y"); ?> Vizoo&nbsp;GmbH
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <!--[if (gte mso 9)|(IE)]>
          </td>
        </tr>
      </table>
      <![endif]-->
            </td>
        </tr>
    </table>
</body>

</html>
<?php

/**
 * Get the inner HTML of an DOM Node
 *
 * @param DOMNode $element The DOM Node.
 *
 * @return string The inner HTML as string.
 */
function getInnerHTML(DOMNode $element)
{
    $innerHTML = "";
    $children = $element->childNodes;

    // go through all children of the DOM Node
    foreach ($children as $child) {
        // set images to be full width and auto height
        if (!empty($child->tagName) && $child->tagName == 'img') {
            $child->setAttribute('width', '100%');
            $child->setAttribute('height', 'auto');
        }

        // get the HTML code for the child
        $innerHTML .= $element->ownerDocument->saveHTML($child);
    }
    return $innerHTML;
}
?>