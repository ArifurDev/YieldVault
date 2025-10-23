<!--begin::Javascript-->
<script>
    var hostUrl = "../../assets/index.html";
</script>

<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.js" }}"></script>
<script src="{{ asset('backend/assets') ."/js/scripts.bundle.js" }}"></script>
<!--end::Global Javascript Bundle-->


<!--begin::Custom Javascript(used for this page only)-->
<script src="{{ asset('backend/assets') ."/js/custom/authentication/sign-in/general.js" }}"></script>
<!--end::Custom Javascript-->

@stack('scripts')

