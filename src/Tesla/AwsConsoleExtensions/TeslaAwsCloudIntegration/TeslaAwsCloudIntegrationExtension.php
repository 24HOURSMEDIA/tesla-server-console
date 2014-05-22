<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 22/05/14
 * Time: 11:52
 */

namespace Tesla\AwsConsoleExtensions\TeslaAwsCloudIntegration;


use Tesla\AwsConsole\Extensions\AbstractExtension;
use Symfony\Component\HttpFoundation\Request;

class TeslaAwsCloudIntegrationExtension extends AbstractExtension {
    /**
     * @return array
     */
    function getDefaultConfiguration()
    {
        return array(
            'aws_cloud_bin_path' => '/bin/aws-cloud'
        );
    }

    private function getStack() {
        exec($this->config['aws_cloud_bin_path'] . ' ec2:show:instances stack --from-cache --format=json', $output);
        $stack = json_decode(implode($output), true);
        return $stack;
    }

    function extendViewParameters(array $parameters)
    {
        $request = $this->container['request'];
        /* @var $request Request */


        $stack = $this->getStack();
        if (!$stack) {
            return;
        }
        $menuItems = array();
        foreach ($stack as $instance) {
            if ($instance['public_dns']) {
           $url = $request->getScheme() . '://' . $instance['public_dns'] . ':' . $request->getPort() .  $request->getRequestUri();
            $menuItems[] = array(
                'title' => $instance['name'],
                'url' => $url,
                'active' => $instance['private_ip'] == $_SERVER['SERVER_ADDR']
            );

            }
        }

        $parameters = parent::extendViewParameters($parameters); // TODO: Change the autogenerated stub
        $extensionParams = array(
            'submenus' => array(
                array(
                    'title' => 'Cloud Stack:',
                    'items' => $menuItems

                )
            )
        );



        $parameters['extensions'] = array_merge_recursive( $parameters['extensions'], $extensionParams);

        return $parameters;
    }


} 