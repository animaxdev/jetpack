<?php
/**
 * The XSL used to style sitemaps is essentially a bunch of
 * static strings. This class handles the construction of
 * those strings.
 *
 * @package Jetpack
 * @since 4.8.0
 */

/**
 * Builds the XSL files required by Jetpack_Sitemap_Manager.
 *
 * @since 4.8.0
 */
class Jetpack_Sitemap_Stylist {

	/**
	 * Convert named entities, strip all HTML except anchor tags,
	 * and interpolate with vsprintf. This is a helper function
	 * for all the internationalized UI strings in this class
	 * which have to include URLs.
	 *
	 * Note that $url_array should be indexed by integers like so:
	 *
	 * array(
	 *   1 => 'example.com',
	 *   2 => 'example.org',
	 * );
	 *
	 * Then '%1$s' in the format string will substitute 'example.com'
	 * and '%2$s' will substitute 'example.org'.
	 *
	 * @access private
	 * @since 4.8.0
	 * @link http://php.net/manual/en/function.vsprintf.php Format string documentation.
	 *
	 * @param string $format A vsprintf-style format string to be sanitized.
	 * @param array  $url_array The string substitution array to be passed to vsprintf.
	 *
	 * @return string The sanitized string.
	 */
	private static function sanitize_with_links( $format, $url_array ) {
		return vsprintf(
			wp_kses(
				ent2ncr( $format ),
				array(
					'a' => array(
						'href'  => true,
						'title' => true,
					),
				)
			),
			$url_array
		);
	}

	/**
	 * Returns the xsl of a sitemap xml file as a string.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return string The contents of the xsl file.
	 */
	public static function sitemap_xsl() {
		$title          = esc_html( ent2ncr( __( 'XML Sitemap', 'jetpack' ) ) );
		$header_url     = esc_html( ent2ncr( __( 'URL', 'jetpack' ) ) );
		$header_lastmod = esc_html( ent2ncr( __( 'Last Modified', 'jetpack' ) ) );

		$description = self::sanitize_with_links(
			__(
				'This is an XML Sitemap generated by <a href="%1$s" rel="noopener noreferrer" target="_blank">Jetpack</a>, meant to be consumed by search engines like <a href="%2$s" rel="noopener noreferrer" target="_blank">Google</a> or <a href="%3$s" rel="noopener noreferrer" target="_blank">Bing</a>.',
				'jetpack'
			),
			array(
				1 => 'http://jetpack.com/',
				2 => 'https://www.google.com/',
				3 => 'https://www.bing.com/',
			)
		);

		$more_info = self::sanitize_with_links(
			__(
				'You can find more information on XML sitemaps at <a href="%1$s" rel="noopener noreferrer" target="_blank">sitemaps.org</a>',
				'jetpack'
			),
			array(
				1 => 'http://sitemaps.org',
			)
		);

		$generated_by = self::sanitize_with_links(
			__(
				'Generated by <a href="%s" rel="noopener noreferrer" target="_blank">Jetpack for WordPress</a>',
				'jetpack'
			),
			array(
				1 => 'https://jetpack.com',
			)
		);

		$css = self::sitemap_xsl_css();

		return <<<XSL
<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version='2.0'
	xmlns:html='http://www.w3.org/TR/REC-html40'
	xmlns:sitemap='http://www.sitemaps.org/schemas/sitemap/0.9'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='html' version='1.0' encoding='UTF-8' indent='yes'/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$title</title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<style type='text/css'>
$css
	</style>
</head>
<body>
	<div id='description'>
		<h1>$title</h1>
		<p>$description</p>
		<p>$more_info</p>
	</div>
	<div id='content'>
		<!-- <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> -->
		<table>
			<tr>
				<th>#</th>
				<th>$header_url</th>
				<th>$header_lastmod</th>
			</tr>
			<xsl:for-each select="sitemap:urlset/sitemap:url">
				<tr>
					<xsl:choose>
						<xsl:when test='position() mod 2 != 1'>
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<td>
						<xsl:value-of select = "position()" />
					</td>
					<td>
						<xsl:variable name='itemURL'>
							<xsl:value-of select='sitemap:loc'/>
						</xsl:variable>
						<a href='{\$itemURL}'>
							<xsl:value-of select='sitemap:loc'/>
						</a>
					</td>
					<td>
						<xsl:value-of select='sitemap:lastmod'/>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</div>
	<div id='footer'>
		<p>$generated_by</p>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>\n
XSL;
	}

	/**
	 * Returns the xsl of a sitemap index xml file as a string.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return string The contents of the xsl file.
	 */
	public static function sitemap_index_xsl() {
		$title          = esc_html( ent2ncr( __( 'XML Sitemap Index', 'jetpack' ) ) );
		$header_url     = esc_html( ent2ncr( __( 'Sitemap URL', 'jetpack' ) ) );
		$header_lastmod = esc_html( ent2ncr( __( 'Last Modified', 'jetpack' ) ) );

		$description = self::sanitize_with_links(
			__(
				'This is an XML Sitemap Index generated by <a href="%1$s" rel="noopener noreferrer" target="_blank">Jetpack</a>, meant to be consumed by search engines like <a href="%2$s" rel="noopener noreferrer" target="_blank">Google</a> or <a href="%3$s" rel="noopener noreferrer" target="_blank">Bing</a>.',
				'jetpack'
			),
			array(
				1 => 'http://jetpack.com/',
				2 => 'https://www.google.com/',
				3 => 'https://www.bing.com/',
			)
		);

		if ( current_user_can( 'manage_options' ) ) {
			$next = human_time_diff( wp_next_scheduled( 'jp_sitemap_cron_hook' ) );
			/* translators: %s is a human_time_diff until next sitemap generation. */
			$no_nodes_warning = sprintf( __( 'No sitemap found. The system will try to build it again in %s.', 'jetpack' ), $next );
		} else {
			$no_nodes_warning = '';
		}

		$more_info = self::sanitize_with_links(
			__(
				'You can find more information on XML sitemaps at <a href="%1$s" rel="noopener noreferrer" target="_blank">sitemaps.org</a>',
				'jetpack'
			),
			array(
				1 => 'http://sitemaps.org',
			)
		);

		$generated_by = self::sanitize_with_links(
			__(
				'Generated by <a href="%s" rel="noopener noreferrer" target="_blank">Jetpack for WordPress</a>',
				'jetpack'
			),
			array(
				1 => 'https://jetpack.com',
			)
		);

		$css = self::sitemap_xsl_css();

		return <<<XSL
<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version='2.0'
	xmlns:html='http://www.w3.org/TR/REC-html40'
	xmlns:sitemap='http://www.sitemaps.org/schemas/sitemap/0.9'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='html' version='1.0' encoding='UTF-8' indent='yes'/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$title</title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<style type='text/css'>
$css
	</style>
</head>
<body>
	<div id='description'>
		<h1>$title</h1>
		<xsl:choose>
			<xsl:when test='not(sitemap:sitemapindex/sitemap:sitemap)'>
				<p><strong>$no_nodes_warning</strong></p>
			</xsl:when>
		</xsl:choose>
		<p>$description</p>
		<p>$more_info</p>
	</div>
	<div id='content'>
		<table>
			<tr>
				<th>#</th>
				<th>$header_url</th>
				<th>$header_lastmod</th>
			</tr>
			<xsl:for-each select='sitemap:sitemapindex/sitemap:sitemap'>
				<tr>
					<xsl:choose>
						<xsl:when test='position() mod 2 != 1'>
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<td>
						<xsl:value-of select = "position()" />
					</td>
					<td>
						<xsl:variable name='itemURL'>
							<xsl:value-of select='sitemap:loc'/>
						</xsl:variable>
						<a href='{\$itemURL}'>
							<xsl:value-of select='sitemap:loc'/>
						</a>
					</td>
					<td>
						<xsl:value-of select='sitemap:lastmod'/>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</div>
	<div id='footer'>
		<p>$generated_by</p>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>\n
XSL;
	}

	/**
	 * Returns the xsl of an image sitemap xml file as a string.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return string The contents of the xsl file.
	 */
	public static function image_sitemap_xsl() {
		$title            = esc_html( ent2ncr( __( 'XML Image Sitemap', 'jetpack' ) ) );
		$header_url       = esc_html( ent2ncr( __( 'Page URL', 'jetpack' ) ) );
		$header_image_url = esc_html( ent2ncr( __( 'Image URL', 'jetpack' ) ) );
		$header_thumbnail = esc_html( ent2ncr( __( 'Thumbnail', 'jetpack' ) ) );
		$header_title     = esc_html( ent2ncr( __( 'Title', 'jetpack' ) ) );
		$header_lastmod   = esc_html( ent2ncr( __( 'Last Modified', 'jetpack' ) ) );
		$header_caption   = esc_html( ent2ncr( __( 'Caption', 'jetpack' ) ) );

		$description = self::sanitize_with_links(
			__(
				'This is an XML Image Sitemap generated by <a href="%1$s" rel="noopener noreferrer" target="_blank">Jetpack</a>, meant to be consumed by search engines like <a href="%2$s" rel="noopener noreferrer" target="_blank">Google</a> or <a href="%3$s" rel="noopener noreferrer" target="_blank">Bing</a>.',
				'jetpack'
			),
			array(
				1 => 'http://jetpack.com/',
				2 => 'https://www.google.com/',
				3 => 'https://www.bing.com/',
			)
		);

		$more_info = self::sanitize_with_links(
			__(
				'You can find more information on XML sitemaps at <a href="%1$s" rel="noopener noreferrer" target="_blank">sitemaps.org</a>',
				'jetpack'
			),
			array(
				1 => 'http://sitemaps.org',
			)
		);

		$generated_by = self::sanitize_with_links(
			__(
				'Generated by <a href="%s" rel="noopener noreferrer" target="_blank">Jetpack for WordPress</a>',
				'jetpack'
			),
			array(
				1 => 'https://jetpack.com',
			)
		);

		$css = self::sitemap_xsl_css();

		return <<<XSL
<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version='2.0'
	xmlns:html='http://www.w3.org/TR/REC-html40'
	xmlns:sitemap='http://www.sitemaps.org/schemas/sitemap/0.9'
	xmlns:image='http://www.google.com/schemas/sitemap-image/1.1'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='html' version='1.0' encoding='UTF-8' indent='yes'/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$title</title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<style type='text/css'>
$css
	</style>
</head>
<body>
	<div id='description'>
		<h1>$title</h1>
		<p>$description</p>
		<p>$more_info</p>
	</div>
	<div id='content'>
		<!-- <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> -->
		<table>
			<tr>
				<th>#</th>
				<th>$header_url</th>
				<th>$header_image_url</th>
				<th>$header_title</th>
				<th>$header_caption</th>
				<th>$header_lastmod</th>
				<th>$header_thumbnail</th>
			</tr>
			<xsl:for-each select="sitemap:urlset/sitemap:url">
				<tr>
					<xsl:choose>
						<xsl:when test='position() mod 2 != 1'>
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<td>
						<xsl:value-of select = "position()" />
					</td>
					<td>
						<xsl:variable name='pageURL'>
							<xsl:value-of select='sitemap:loc'/>
						</xsl:variable>
						<a href='{\$pageURL}'>
							<xsl:value-of select='sitemap:loc'/>
						</a>
					</td>
					<xsl:variable name='itemURL'>
						<xsl:value-of select='image:image/image:loc'/>
					</xsl:variable>
					<td>
						<a href='{\$itemURL}'>
							<xsl:value-of select='image:image/image:loc'/>
						</a>
					</td>
					<td>
						<xsl:value-of select='image:image/image:title'/>
					</td>
					<td>
						<xsl:value-of select='image:image/image:caption'/>
					</td>
					<td>
						<xsl:value-of select='sitemap:lastmod'/>
					</td>
					<td>
						<a href='{\$itemURL}'>
							<img class='thumbnail' src='{\$itemURL}'/>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</div>
	<div id='footer'>
		<p>$generated_by</p>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>\n
XSL;
	}

	/**
	 * Returns the xsl of a video sitemap xml file as a string.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return string The contents of the xsl file.
	 */
	public static function video_sitemap_xsl() {
		$title              = esc_html( ent2ncr( __( 'XML Video Sitemap', 'jetpack' ) ) );
		$header_url         = esc_html( ent2ncr( __( 'Page URL', 'jetpack' ) ) );
		$header_image_url   = esc_html( ent2ncr( __( 'Video URL', 'jetpack' ) ) );
		$header_thumbnail   = esc_html( ent2ncr( __( 'Thumbnail', 'jetpack' ) ) );
		$header_title       = esc_html( ent2ncr( __( 'Title', 'jetpack' ) ) );
		$header_lastmod     = esc_html( ent2ncr( __( 'Last Modified', 'jetpack' ) ) );
		$header_description = esc_html( ent2ncr( __( 'Description', 'jetpack' ) ) );

		$description = self::sanitize_with_links(
			__(
				'This is an XML Video Sitemap generated by <a href="%1$s" rel="noopener noreferrer" target="_blank">Jetpack</a>, meant to be consumed by search engines like <a href="%2$s" rel="noopener noreferrer" target="_blank">Google</a> or <a href="%3$s" rel="noopener noreferrer" target="_blank">Bing</a>.',
				'jetpack'
			),
			array(
				1 => 'http://jetpack.com/',
				2 => 'https://www.google.com/',
				3 => 'https://www.bing.com/',
			)
		);

		$more_info = self::sanitize_with_links(
			__(
				'You can find more information on XML sitemaps at <a href="%1$s" rel="noopener noreferrer" target="_blank">sitemaps.org</a>',
				'jetpack'
			),
			array(
				1 => 'http://sitemaps.org',
			)
		);

		$generated_by = self::sanitize_with_links(
			__(
				'Generated by <a href="%s" rel="noopener noreferrer" target="_blank">Jetpack for WordPress</a>',
				'jetpack'
			),
			array(
				1 => 'https://jetpack.com',
			)
		);

		$css = self::sitemap_xsl_css();

		return <<<XSL
<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version='2.0'
	xmlns:html='http://www.w3.org/TR/REC-html40'
	xmlns:sitemap='http://www.sitemaps.org/schemas/sitemap/0.9'
	xmlns:video='http://www.google.com/schemas/sitemap-video/1.1'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='html' version='1.0' encoding='UTF-8' indent='yes'/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$title</title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<style type='text/css'>
$css
	</style>
</head>
<body>
	<div id='description'>
		<h1>$title</h1>
		<p>$description</p>
		<p>$more_info</p>
	</div>
	<div id='content'>
		<!-- <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> -->
		<table>
			<tr>
				<th>#</th>
				<th>$header_url</th>
				<th>$header_image_url</th>
				<th>$header_title</th>
				<th>$header_description</th>
				<th>$header_lastmod</th>
				<th>$header_thumbnail</th>
			</tr>
			<xsl:for-each select="sitemap:urlset/sitemap:url">
				<tr>
					<xsl:choose>
						<xsl:when test='position() mod 2 != 1'>
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<td>
						<xsl:value-of select = "position()" />
					</td>
					<td>
						<xsl:variable name='pageURL'>
							<xsl:value-of select='sitemap:loc'/>
						</xsl:variable>
						<a href='{\$pageURL}'>
							<xsl:value-of select='sitemap:loc'/>
						</a>
					</td>
					<xsl:variable name='itemURL'>
						<xsl:value-of select='video:video/video:content_loc'/>
					</xsl:variable>
					<td>
						<a href='{\$itemURL}'>
							<xsl:value-of select='video:video/video:content_loc'/>
						</a>
					</td>
					<td>
						<xsl:value-of select='video:video/video:title'/>
					</td>
					<td>
						<xsl:value-of select='video:video/video:description' disable-output-escaping='yes'/>
					</td>
					<td>
						<xsl:value-of select='sitemap:lastmod'/>
					</td>
					<td>
						<xsl:variable name='thumbURL'>
							<xsl:value-of select='video:video/video:thumbnail_loc'/>
						</xsl:variable>
						<a href='{\$thumbURL}'>
							<img class='thumbnail' src='{\$thumbURL}'/>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</div>
	<div id='footer'>
		<p>$generated_by</p>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>\n
XSL;
	}

	/**
	 * Returns the xsl of a news sitemap xml file as a string.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return string The contents of the xsl file.
	 */
	public static function news_sitemap_xsl() {
		$title          = esc_html( ent2ncr( __( 'XML News Sitemap', 'jetpack' ) ) );
		$header_url     = esc_html( ent2ncr( __( 'Page URL', 'jetpack' ) ) );
		$header_title   = esc_html( ent2ncr( __( 'Title', 'jetpack' ) ) );
		$header_pubdate = esc_html( ent2ncr( __( 'Publication Date', 'jetpack' ) ) );

		$description = self::sanitize_with_links(
			__(
				'This is an XML News Sitemap generated by <a href="%1$s" rel="noopener noreferrer" target="_blank">Jetpack</a>, meant to be consumed by search engines like <a href="%2$s" rel="noopener noreferrer" target="_blank">Google</a> or <a href="%3$s" rel="noopener noreferrer" target="_blank">Bing</a>.',
				'jetpack'
			),
			array(
				1 => 'http://jetpack.com/',
				2 => 'https://www.google.com/',
				3 => 'https://www.bing.com/',
			)
		);

		$more_info = self::sanitize_with_links(
			__(
				'You can find more information on XML sitemaps at <a href="%1$s" rel="noopener noreferrer" target="_blank">sitemaps.org</a>',
				'jetpack'
			),
			array(
				1 => 'http://sitemaps.org',
			)
		);

		$generated_by = self::sanitize_with_links(
			__(
				'Generated by <a href="%s" rel="noopener noreferrer" target="_blank">Jetpack for WordPress</a>',
				'jetpack'
			),
			array(
				1 => 'https://jetpack.com',
			)
		);

		$css = self::sitemap_xsl_css();

		return <<<XSL
<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version='2.0'
	xmlns:html='http://www.w3.org/TR/REC-html40'
	xmlns:sitemap='http://www.sitemaps.org/schemas/sitemap/0.9'
	xmlns:news='http://www.google.com/schemas/sitemap-news/0.9'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='html' version='1.0' encoding='UTF-8' indent='yes'/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$title</title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<style type='text/css'>
$css
	</style>
</head>
<body>
	<div id='description'>
		<h1>$title</h1>
		<p>$description</p>
		<p>$more_info</p>
	</div>
	<div id='content'>
		<!-- <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> -->
		<table>
			<tr>
				<th>#</th>
				<th>$header_url</th>
				<th>$header_title</th>
				<th>$header_pubdate</th>
			</tr>
			<xsl:for-each select="sitemap:urlset/sitemap:url">
				<tr>
					<xsl:choose>
						<xsl:when test='position() mod 2 != 1'>
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<td>
						<xsl:value-of select = "position()" />
					</td>
					<xsl:variable name='pageURL'>
						<xsl:value-of select='sitemap:loc'/>
					</xsl:variable>
					<td>
						<a href='{\$pageURL}'>
							<xsl:value-of select='sitemap:loc'/>
						</a>
					</td>
					<td>
						<a href='{\$pageURL}'>
							<xsl:value-of select='news:news/news:title'/>
						</a>
					</td>
					<td>
						<xsl:value-of select='news:news/news:publication_date'/>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</div>
	<div id='footer'>
		<p>$generated_by</p>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>\n
XSL;
	}

	/**
	 * The CSS to be included in sitemap xsl stylesheets;
	 * factored out for uniformity.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return string The CSS.
	 */
	public static function sitemap_xsl_css() {
		return <<<CSS
		body {
			font: 14px 'Open Sans', Helvetica, Arial, sans-serif;
			margin: 0;
		}

		a {
			color: #3498db;
			text-decoration: none;
		}

		h1 {
			margin: 0;
		}

		#description {
			background-color: #81a844;
			color: #FFF;
			padding: 30px 30px 20px;
		}

		#description a {
			color: #fff;
		}

		#content {
			padding: 10px 30px 30px;
			background: #fff;
		}

		a:hover {
			border-bottom: 1px solid;
		}

		th, td {
			font-size: 12px;
		}

		th {
			text-align: left;
			border-bottom: 1px solid #ccc;
		}

		th, td {
			padding: 10px 15px;
		}

		.odd {
			background-color: #E7F1D4;
		}

		#footer {
			margin: 20px 30px;
			font-size: 12px;
			color: #999;
		}

		#footer a {
			color: inherit;
		}

		#description a, #footer a {
			border-bottom: 1px solid;
		}

		#description a:hover, #footer a:hover {
			border-bottom: none;
		}

		img.thumbnail {
			max-height: 100px;
			max-width: 100px;
		}
CSS;
	}

}
