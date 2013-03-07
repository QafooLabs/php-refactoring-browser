Refactoring Domain
------------------

Automatic code refactoring based on PHP Parser ASTs. Data from the type
inference domain is used in a format suitable for the refactoring domain.

The refactoring domain is based on William Opdyke thesis on `"Refactoring
Object-Oriented Frameworks"
<http://www.laputan.org/pub/papers/opdyke-thesis.pdf>`_.

Type Inference Domain
---------------------

Just necessary because PHP is not statically typed. The type inference domain
creates a database of artifacts, which is loaded into the refactoring domain
through an anticorruption layer.
