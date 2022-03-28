export default {
    bind(el, binding, vnode) {
        document.addEventListener('click', (event) => {
            vnode.context[ binding.expression ](event)
        })
    }
}
