<?php
/**
 * This file is part of PHP Mess Detector.
 *
 * Copyright (c) 2008-2017, Manuel Pichler <mapi@phpmd.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD;

use PHPMD\Renderer\HTMLRenderer;
use PHPMD\Renderer\TextRenderer;
use PHPMD\Renderer\XMLRenderer;

/**
 * Factory responsible for creating PHPMD rendering engines.
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class RendererFactory
{
    /**
     * Creates a report renderer instance based on the user's command line
     * argument.
     *
     * Valid renderers are:
     * <ul>
     *   <li>xml</li>
     *   <li>html</li>
     *   <li>text</li>
     * </ul>
     *
     * @param string $reportFormat
     * @return \PHPMD\AbstractRenderer
     * @throws \InvalidArgumentException When the specified renderer does not exist.
     */
    public static function createRenderer($reportFormat)
    {
        switch ($reportFormat) {
            case 'xml':
                return new XMLRenderer();
            case 'html':
                return new HTMLRenderer();
            case 'text':
                return new TextRenderer();
            default:
                return static::createCustomRenderer($reportFormat);
        }
    }

    /**
     * @param string $reportFormat
     * @return \PHPMD\AbstractRenderer
     * @throws \InvalidArgumentException
     */
    private static function createCustomRenderer($reportFormat)
    {
        if ('' === $reportFormat) {
            throw new \InvalidArgumentException(
                'Can\'t create report with empty format.'
            );
        }

        if (class_exists($reportFormat)) {
            return new $reportFormat();
        }

        // Try to load a custom renderer
        $fileName = strtr($reportFormat, '_\\', '//') . '.php';

        $fileHandle = @fopen($fileName, 'r', true);
        if (is_resource($fileHandle) === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Can\'t find the custom report class: %s',
                    $reportFormat
                )
            );
        }
        @fclose($fileHandle);

        include_once $fileName;

        return new $reportFormat();
    }
}
