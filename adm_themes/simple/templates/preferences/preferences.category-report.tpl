<form {foreach $attributes as $attribute}
        {$attribute@key}="{$attribute}"
    {/foreach}>

    {include 'sys-template-parts/form.input.tpl' data=$elements['admidio-csrf-token']}
    {include 'sys-template-parts/form.checkbox.tpl' data=$elements['category_report_enable_module']}
    {include 'sys-template-parts/form.select.tpl' data=$elements['category_report_default_configuration']}
    {include 'sys-template-parts/form.button.tpl' data=$elements['btn_save_category_report']}
    <div class="form-alert" style="display: none;">&nbsp;</div>
</form>
