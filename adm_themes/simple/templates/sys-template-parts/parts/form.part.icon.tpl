{if $icon}
    {if {isFontAwesomeIcon icon=$icon}}
        <i class="{$icon} fas" {if isset($label)}data-toggle="tooltip" title="{$label}"{/if}></i>
    {else}
        <img src="{$icon}" {if isset($label)}alt="{$label}"{/if} />
    {/if}
{/if}
